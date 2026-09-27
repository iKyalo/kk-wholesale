<?php
namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Feature tests for App\Http\Controllers\UsersController.
 *
 * ---------------------------------------------------------------------------
 * NOTE ON A PREVIOUS REVISION: an earlier version of this controller gated
 * update()'s branch/store sync on `$user->role === 2/3`, comparing a
 * relationship accessor to a bare integer -- which meant it (almost
 * certainly) always evaluated false and wiped every assignment on every
 * save. This revision fixes that by using `(int) $user->role_id`, matching
 * the pattern already used elsewhere. The tests below verify the corrected
 * behavior: branches/stores are synced for the matching manager role and
 * cleared when the role is (or becomes) anything else.
 * ---------------------------------------------------------------------------
 *
 * ASSUMPTIONS (nothing below could be verified from the controller alone).
 * Each one lives in a constant or helper at the top of the class:
 *
 *  A1. Factories exist: User, Role, Branch, Store.
 *  A2. Route names users.{index,create,store,show,edit,update,destroy}
 *      (users.index is confirmed by the controller's redirects).
 *  A3. Routes sit behind `auth`; guests redirect to route('login').
 *  A4. Role has `name` and `slug` columns. store() keys eligibility for branch/
 *      store assignment off `role->slug === 'branch_manager'` /
 *      'store_manager'. update() keys it off `(int) $user->role_id === 2 / 3`.
 *      The controller confirms role_id 2 = branch manager and 3 = store
 *      manager; matching this to BranchesController::editUser() (role_id 2)
 *      and StoresController::editUser() (role_id 3) elsewhere in this app, I
 *      assume role id 1 = a plain/staff role (slug 'staff', irrelevant to
 *      assignment) and that these role_id values correspond to the slugs
 *      'branch_manager' / 'store_manager' used in store(). If your actual
 *      slugs or ids differ, adjust ROLE_* constants and makeRole() calls.
 *  A5. User::role() is a belongsTo(Role) relationship; User::branches() and
 *      User::stores() are belongsToMany; `users.status` is a plain string
 *      column with no enum constraint beyond the validator ('active'/
 *      'inactive'); User does NOT use SoftDeletes (else swap
 *      assertDatabaseMissing for assertSoftDeleted).
 *  A6. PHPUnit 10+ (attribute data providers).
 * ---------------------------------------------------------------------------
 */
class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ROLE_STAFF_ID          = 1; // A4
    private const ROLE_BRANCH_MANAGER_ID = 2; // A4
    private const ROLE_STORE_MANAGER_ID  = 3; // A4

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->create(); // A3 - any authenticated user
    }

    /* =====================================================================
     | Helpers
     * =================================================================== */

    private function signedIn(): static
    {
        return $this->actingAs($this->actor);
    }

    private function makeRole(int $id, string $slug, ?string $name = null): Role
    {
        return Role::firstOrCreate(
            ['id' => $id],
            [
                'slug' => $slug,
                'name' => $name ?? ucfirst(str_replace('_', ' ', $slug)),
            ]
        );
    }

    private function staffRole(): Role
    {
        return $this->makeRole(self::ROLE_STAFF_ID, 'staff');
    }

    private function branchManagerRole(): Role
    {
        return $this->makeRole(self::ROLE_BRANCH_MANAGER_ID, 'branch_manager');
    }

    private function storeManagerRole(): Role
    {
        return $this->makeRole(self::ROLE_STORE_MANAGER_ID, 'store_manager');
    }

    private function validPayload(Role $role, array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'phone'                 => '+254700111222',
            'password'              => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
            'role'                  => $role->id,
            'status'                => 'active',
        ], $overrides);
    }

    private function assignedBranchIds(User $user): array
    {
        return $user->branches()->pluck('branches.id')->all();
    }

    private function assignedStoreIds(User $user): array
    {
        return $user->stores()->pluck('stores.id')->all();
    }

    private function listedIds(TestResponse $response): array
    {
        return $response->viewData('users')->pluck('id')->all();
    }

    private function missingUserId(): int
    {
        return (int) User::max('id') + 1;
    }

    private function missingRoleId(): int
    {
        return (int) Role::max('id') + 1;
    }

    private function missingBranchId(): int
    {
        return (int) Branch::max('id') + 1;
    }

    private function missingStoreId(): int
    {
        return (int) Store::max('id') + 1;
    }

    /* =====================================================================
     | Data providers
     * =================================================================== */

    public static function requiredFieldsForCreate(): array
    {
        return [
            'name'     => ['name'],
            'email'    => ['email'],
            'phone'    => ['phone'],
            'password' => ['password'],
            'role'     => ['role'],
            'status'   => ['status'],
        ];
    }

    public static function requiredFieldsForUpdate(): array
    {
        return [
            'name'   => ['name'],
            'email'  => ['email'],
            'phone'  => ['phone'],
            'role'   => ['role'],
            'status' => ['status'],
        ];
    }

    public static function invalidPhoneNumbers(): array
    {
        return [
            'contains letters'    => ['0712abc456'],
            'contains a hash'     => ['0712#456'],
            'contains an at-sign' => ['user@0712456'],
        ];
    }

    public static function invalidStatusValues(): array
    {
        return [
            'empty string' => [''],
            'random word'  => ['pending'],
            'wrong case'   => ['Active'],
            'boolean-ish'  => ['1'],
        ];
    }

    public static function searchableColumns(): array
    {
        return [
            'name'  => ['name'],
            'email' => ['email'],
            'phone' => ['phone'],
        ];
    }

    /* =====================================================================
     | Authentication
     * =================================================================== */

    public function test_guests_are_redirected_to_login_for_every_action(): void
    {
        $role   = $this->staffRole();
        $target = User::factory()->create();

        $requests = [
            ['GET', route('users.index')],
            ['GET', route('users.create')],
            ['POST', route('users.store')],
            ['GET', route('users.show', $target)],
            ['GET', route('users.edit', $target)],
            ['PUT', route('users.update', $target)],
            ['DELETE', route('users.destroy', $target)],
        ];

        foreach ($requests as [$method, $url]) {
            $response = $this->call($method, $url, $method === 'POST' ? $this->validPayload($role) : []);

            $this->assertTrue(
                $response->isRedirect(route('login')),
                "Guest was not redirected to login for {$method} {$url} (status {$response->getStatusCode()})."
            );
        }

        $this->assertModelExists($target);
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    /* =====================================================================
     | index()
     * =================================================================== */

    public function test_index_lists_users_newest_first_with_relations_loaded(): void
    {
        $role  = $this->staffRole();
        $older = User::factory()->create(['role_id' => $role->id, 'created_at' => now()->subDays(2)]);
        $newer = User::factory()->create(['role_id' => $role->id, 'created_at' => now()->subDay()]);

        $response = $this->signedIn()
            ->get(route('users.index'))
            ->assertOk()
            ->assertViewIs('users.index')
            ->assertViewHasAll(['users', 'roles', 'branches', 'stores']);

        $users = $response->viewData('users');
        $ids   = $users->pluck('id')->all();

        // $newer was created after $older and after the signed-in actor, so it must lead.
        $this->assertSame($newer->id, $ids[0]);
        $this->assertContains($older->id, $ids);
        $this->assertTrue($users->first()->relationLoaded('role'));
        $this->assertTrue($users->first()->relationLoaded('branches'));
        $this->assertTrue($users->first()->relationLoaded('stores'));
    }

    public function test_index_passes_roles_keyed_by_id(): void
    {
        $role = $this->branchManagerRole();

        $response = $this->signedIn()->get(route('users.index'))->assertOk();

        $roles = $response->viewData('roles');

        $this->assertTrue($roles->has($role->id));
        $this->assertSame($role->name, $roles->get($role->id)->name);
    }

    public function test_index_passes_only_active_branches_and_stores_sorted_by_name(): void
    {
        $activeA = Branch::factory()->create(['name' => 'Zulu Branch', 'is_active' => true]);
        $activeB = Branch::factory()->create(['name' => 'Alpha Branch', 'is_active' => true]);
        Branch::factory()->create(['is_active' => false]);

        $activeStore = Store::factory()->create(['is_active' => true, 'branch_id' => $activeA->id]);
        Store::factory()->create(['is_active' => false, 'branch_id' => $activeA->id]);

        $response = $this->signedIn()->get(route('users.index'))->assertOk();

        $this->assertSame(
            ['Alpha Branch', 'Zulu Branch'],
            $response->viewData('branches')->pluck('name')->all()
        );
        $this->assertEqualsCanonicalizing([$activeStore->id], $response->viewData('stores')->pluck('id')->all());
    }

    #[DataProvider('searchableColumns')]
    public function test_index_search_matches_name_email_and_phone(string $column): void
    {
        $role  = $this->staffRole();
        $match = User::factory()->create([$column => 'Findable Term', 'role_id' => $role->id]);

        $response = $this->signedIn()->get(route('users.index', ['search' => 'Findable']))->assertOk();

        $ids = $this->listedIds($response);
        $this->assertContains($match->id, $ids);
        $this->assertNotContains($this->actor->id, $ids); // actor doesn't match the search term
    }

    public function test_index_filters_by_role(): void
    {
        $staff       = $this->staffRole();
        $manager     = $this->branchManagerRole();
        $staffUser   = User::factory()->create(['role_id' => $staff->id]);
        $managerUser = User::factory()->create(['role_id' => $manager->id]);

        $response = $this->signedIn()->get(route('users.index', ['role' => $manager->id]))->assertOk();

        $ids = $this->listedIds($response);
        $this->assertContains($managerUser->id, $ids);
        $this->assertNotContains($staffUser->id, $ids);
    }

    public function test_index_filters_by_status(): void
    {
        $role     = $this->staffRole();
        $active   = User::factory()->create(['role_id' => $role->id, 'status' => 'active']);
        $inactive = User::factory()->create(['role_id' => $role->id, 'status' => 'inactive']);

        $response = $this->signedIn()->get(route('users.index', ['status' => 'inactive']))->assertOk();

        $ids = $this->listedIds($response);
        $this->assertContains($inactive->id, $ids);
        $this->assertNotContains($active->id, $ids);
    }

    public function test_index_status_filter_is_an_exact_match_with_no_enum_restriction(): void
    {
        $role = $this->staffRole();
        User::factory()->create(['role_id' => $role->id, 'status' => 'active']);

        $response = $this->signedIn()->get(route('users.index', ['status' => 'not-a-real-status']))->assertOk();

        $this->assertSame([], array_diff($this->listedIds($response), [$this->actor->id]));
    }

    public function test_index_filters_by_assigned_branch(): void
    {
        $role     = $this->branchManagerRole();
        $branch   = Branch::factory()->create();
        $other    = Branch::factory()->create();
        $inBranch = User::factory()->create(['role_id' => $role->id]);
        $inBranch->branches()->attach($branch);
        $elsewhere = User::factory()->create(['role_id' => $role->id]);
        $elsewhere->branches()->attach($other);

        $response = $this->signedIn()->get(route('users.index', ['branch_id' => $branch->id]))->assertOk();

        $ids = $this->listedIds($response);
        $this->assertContains($inBranch->id, $ids);
        $this->assertNotContains($elsewhere->id, $ids);
    }

    public function test_index_filters_by_assigned_store(): void
    {
        $role    = $this->storeManagerRole();
        $store   = Store::factory()->create(['branch_id' => Branch::factory()->create()->id]);
        $other   = Store::factory()->create(['branch_id' => Branch::factory()->create()->id]);
        $inStore = User::factory()->create(['role_id' => $role->id]);
        $inStore->stores()->attach($store);
        $elsewhere = User::factory()->create(['role_id' => $role->id]);
        $elsewhere->stores()->attach($other);

        $response = $this->signedIn()->get(route('users.index', ['store_id' => $store->id]))->assertOk();

        $ids = $this->listedIds($response);
        $this->assertContains($inStore->id, $ids);
        $this->assertNotContains($elsewhere->id, $ids);
    }

    public function test_index_blank_filters_are_ignored(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->signedIn()
            ->get(route('users.index', ['search' => '', 'role' => '', 'status' => '', 'branch_id' => '', 'store_id' => '']))
            ->assertOk();

        $this->assertContains($user->id, $this->listedIds($response));
    }

    public function test_index_paginates_fifteen_per_page_and_keeps_query_string(): void
    {
        $role = $this->staffRole();

        foreach (range(1, 16) as $i) {
            User::factory()->create(['role_id' => $role->id, 'name' => sprintf('Bulk %02d', $i)]);
        }

        $page1 = $this->signedIn()
            ->get(route('users.index', ['search' => 'Bulk']))
            ->assertOk()
            ->viewData('users');

        $this->assertCount(15, $page1);
        $this->assertSame(16, $page1->total());
        $this->assertStringContainsString('search=Bulk', $page1->nextPageUrl());
    }

    /* =====================================================================
     | create()
     * =================================================================== */

    public function test_create_displays_the_form_with_active_branches_stores_and_all_roles(): void
    {
        $activeBranch = Branch::factory()->create(['is_active' => true]);
        Branch::factory()->create(['is_active' => false]);
        $activeStore = Store::factory()->create(['is_active' => true, 'branch_id' => $activeBranch->id]);
        Store::factory()->create(['is_active' => false, 'branch_id' => $activeBranch->id]);
        $role = $this->staffRole();

        $response = $this->signedIn()
            ->get(route('users.create'))
            ->assertOk()
            ->assertViewIs('users.create')
            ->assertViewHasAll(['branches', 'stores', 'roles']);

        $this->assertEqualsCanonicalizing([$activeBranch->id], $response->viewData('branches')->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$activeStore->id], $response->viewData('stores')->pluck('id')->all());
        $this->assertTrue($response->viewData('roles')->contains('id', $role->id));
    }

    /* =====================================================================
     | store()
     * =================================================================== */

    public function test_store_creates_a_staff_user_with_hashed_password(): void
    {
        $role   = $this->staffRole();
        $before = User::count();

        $this->signedIn()
            ->post(route('users.store'), $this->validPayload($role))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User created successfully.')
            ->assertSessionHasNoErrors();

        $this->assertSame($before + 1, User::count());

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame($role->id, $user->role_id);
        $this->assertSame('active', $user->status);
        $this->assertTrue(Hash::check('a-strong-password', $user->password));
    }

    public function test_store_assigns_branches_for_a_branch_manager(): void
    {
        $role      = $this->branchManagerRole();
        [$b1, $b2] = Branch::factory()->count(2)->create();

        $this->signedIn()
            ->post(route('users.store'), $this->validPayload($role, ['branch_ids' => [$b1->id, $b2->id]]))
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertEqualsCanonicalizing([$b1->id, $b2->id], $this->assignedBranchIds($user));
        $this->assertSame([], $this->assignedStoreIds($user));
    }

    public function test_store_assigns_no_branches_for_a_branch_manager_when_none_are_submitted(): void
    {
        $role = $this->branchManagerRole();

        $this->signedIn()->post(route('users.store'), $this->validPayload($role));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame([], $this->assignedBranchIds($user));
    }

    public function test_store_assigns_stores_for_a_store_manager(): void
    {
        $role      = $this->storeManagerRole();
        $branch    = Branch::factory()->create();
        [$s1, $s2] = Store::factory()->count(2)->create(['branch_id' => $branch->id]);

        $this->signedIn()
            ->post(route('users.store'), $this->validPayload($role, ['store_ids' => [$s1->id, $s2->id]]))
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertEqualsCanonicalizing([$s1->id, $s2->id], $this->assignedStoreIds($user));
        $this->assertSame([], $this->assignedBranchIds($user));
    }

    public function test_store_ignores_branch_and_store_ids_for_a_role_that_is_neither_manager_type(): void
    {
        $role   = $this->staffRole();
        $branch = Branch::factory()->create();
        $store  = Store::factory()->create(['branch_id' => $branch->id]);

        $this->signedIn()->post(route('users.store'), $this->validPayload($role, [
            'branch_ids' => [$branch->id],
            'store_ids'  => [$store->id],
        ]));

        $user = User::where('email', 'jane@example.com')->firstOrFail();
        $this->assertSame([], $this->assignedBranchIds($user));
        $this->assertSame([], $this->assignedStoreIds($user));
    }

    #[DataProvider('requiredFieldsForCreate')]
    public function test_store_requires_field_when_missing(string $field): void
    {
        $role    = $this->staffRole();
        $before  = User::count();
        $payload = $this->validPayload($role);
        unset($payload[$field]);

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $payload)
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, User::count());
    }

    public function test_store_rejects_a_duplicate_email(): void
    {
        $role = $this->staffRole();
        User::factory()->create(['email' => 'jane@example.com']);
        $before = User::count();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('email');

        $this->assertSame($before, User::count());
    }

    public function test_store_rejects_a_duplicate_phone(): void
    {
        $role = $this->staffRole();
        User::factory()->create(['phone' => '+254700111222']);
        $before = User::count();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('phone');

        $this->assertSame($before, User::count());
    }

    #[DataProvider('invalidPhoneNumbers')]
    public function test_store_rejects_a_phone_number_with_disallowed_characters(string $phone): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, ['phone' => $phone]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('phone');
    }

    public function test_store_rejects_a_password_shorter_than_8_characters(): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, [
                'password' => 'short12', 'password_confirmation' => 'short12',
            ]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('password');
    }

    public function test_store_rejects_a_password_confirmation_mismatch(): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, [
                'password' => 'a-strong-password', 'password_confirmation' => 'different',
            ]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('password');
    }

    public function test_store_rejects_a_role_that_does_not_exist(): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, ['role' => $this->missingRoleId()]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('role');
    }

    #[DataProvider('invalidStatusValues')]
    public function test_store_rejects_an_invalid_status_value(string $status): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, ['status' => $status]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('status');
    }

    public function test_store_accepts_the_status_inactive(): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->post(route('users.store'), $this->validPayload($role, ['status' => 'inactive']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'status' => 'inactive']);
    }

    public function test_store_rejects_a_branch_id_that_does_not_exist(): void
    {
        $role = $this->branchManagerRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, ['branch_ids' => [$this->missingBranchId()]]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('branch_ids.0');
    }

    public function test_store_rejects_a_store_id_that_does_not_exist(): void
    {
        $role = $this->storeManagerRole();

        $this->signedIn()
            ->from(route('users.create'))
            ->post(route('users.store'), $this->validPayload($role, ['store_ids' => [$this->missingStoreId()]]))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('store_ids.0');
    }

    public function test_store_accepts_a_name_at_the_255_character_boundary(): void
    {
        $role = $this->staffRole();
        $name = str_repeat('a', 255);

        $this->signedIn()
            ->post(route('users.store'), $this->validPayload($role, ['name' => $name]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'name' => $name]);
    }

    /* =====================================================================
     | show()
     * =================================================================== */

    public function test_show_displays_the_user_with_branches_and_stores_loaded(): void
    {
        $role = $this->branchManagerRole();
        $user = User::factory()->create(['role_id' => $role->id]);
        $user->branches()->attach(Branch::factory()->create());

        $response = $this->signedIn()
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertViewIs('users.show')
            ->assertViewHas('user');

        $shown = $response->viewData('user');
        $this->assertTrue($shown->is($user));
        $this->assertTrue($shown->relationLoaded('branches'));
        $this->assertTrue($shown->relationLoaded('stores'));
    }

    public function test_show_returns_404_for_a_missing_user(): void
    {
        $this->signedIn()->get(route('users.show', $this->missingUserId()))->assertNotFound();
    }

    /* =====================================================================
     | edit()
     * =================================================================== */

    public function test_edit_displays_the_form_with_all_branches_and_stores_including_inactive(): void
    {
        $role           = $this->staffRole();
        $user           = User::factory()->create(['role_id' => $role->id]);
        $activeBranch   = Branch::factory()->create(['is_active' => true]);
        $inactiveBranch = Branch::factory()->create(['is_active' => false]);
        $inactiveStore  = Store::factory()->create(['is_active' => false, 'branch_id' => $activeBranch->id]);

        $response = $this->signedIn()
            ->get(route('users.edit', $user))
            ->assertOk()
            ->assertViewIs('users.edit')
            ->assertViewHas('user', fn($shown) => $shown->is($user));

        $this->assertEqualsCanonicalizing(
            [$activeBranch->id, $inactiveBranch->id],
            $response->viewData('branches')->pluck('id')->all()
        );
        $this->assertContains($inactiveStore->id, $response->viewData('stores')->pluck('id')->all());
    }

    public function test_edit_returns_404_for_a_missing_user(): void
    {
        $this->signedIn()->get(route('users.edit', $this->missingUserId()))->assertNotFound();
    }

    /* =====================================================================
     | update()
     * =================================================================== */

    public function test_update_changes_profile_fields_and_leaves_other_users_alone(): void
    {
        $oldRole = $this->staffRole();
        $newRole = $this->branchManagerRole();
        $user    = User::factory()->create([
            'name'    => 'Old', 'email'         => 'old@example.com', 'phone' => '0700000000',
            'role_id' => $oldRole->id, 'status' => 'active',
        ]);
        $other = User::factory()->create(['name' => 'Untouched']);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name' => 'New', 'email'         => 'new@example.com', 'phone' => '0711111111',
                'role' => $newRole->id, 'status' => 'inactive',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User updated successfully.')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id'    => $user->id, 'name'       => 'New', 'email'         => 'new@example.com',
            'phone' => '0711111111', 'role_id' => $newRole->id, 'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => 'Untouched']);
    }

    public function test_update_without_a_password_keeps_the_existing_password(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'password' => Hash::make('original-password')]);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role' => $role->id, 'status'  => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertTrue(Hash::check('original-password', $user->fresh()->password));
    }

    public function test_update_with_a_new_password_changes_it_and_hashes_it(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'password' => Hash::make('original-password')]);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name'     => $user->name, 'email'                          => $user->email, 'phone' => $user->phone,
                'role'     => $role->id, 'status'                           => 'active',
                'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password',
            ])
            ->assertRedirect(route('users.index'));

        $fresh = $user->fresh();
        $this->assertFalse(Hash::check('original-password', $fresh->password));
        $this->assertTrue(Hash::check('brand-new-password', $fresh->password));
    }

    public function test_update_allows_keeping_the_users_own_email_and_phone(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'email' => 'keep@example.com', 'phone' => '0700000000']);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name' => 'Renamed', 'email'  => 'keep@example.com', 'phone' => '0700000000',
                'role' => $role->id, 'status' => 'active',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Renamed']);
    }

    public function test_update_rejects_an_email_used_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'email' => 'mine@example.com', 'name' => 'Original']);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => 'Changed', 'email'  => 'taken@example.com', 'phone' => $user->phone,
                'role' => $role->id, 'status' => 'active',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original']);
    }

    public function test_update_rejects_a_phone_used_by_another_user(): void
    {
        User::factory()->create(['phone' => '+254700999888']);
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'name' => 'Original']);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => 'Changed', 'email'  => $user->email, 'phone' => '+254700999888',
                'role' => $role->id, 'status' => 'active',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('phone');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original']);
    }

    #[DataProvider('invalidPhoneNumbers')]
    public function test_update_rejects_a_phone_number_with_disallowed_characters(string $phone): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $phone,
                'role' => $role->id, 'status'  => 'active',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('phone');
    }

    public function test_update_rejects_a_new_password_shorter_than_8_characters(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name'     => $user->name, 'email'               => $user->email, 'phone' => $user->phone,
                'role'     => $role->id, 'status'                => 'active',
                'password' => 'short12', 'password_confirmation' => 'short12',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('password');
    }

    public function test_update_rejects_a_new_password_without_confirmation(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id, 'password' => Hash::make('original-password')]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role' => $role->id, 'status'  => 'active', 'password'  => 'a-new-password',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('original-password', $user->fresh()->password));
    }

    #[DataProvider('requiredFieldsForUpdate')]
    public function test_update_requires_field_when_missing(string $field): void
    {
        $role    = $this->staffRole();
        $user    = User::factory()->create(['role_id' => $role->id, 'name' => 'Original']);
        $payload = [
            'name' => 'Changed', 'email'  => 'changed@example.com', 'phone' => '0722000000',
            'role' => $role->id, 'status' => 'active',
        ];
        unset($payload[$field === 'role' ? 'role' : $field]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), $payload)
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original']);
    }

    #[DataProvider('invalidStatusValues')]
    public function test_update_rejects_an_invalid_status_value(string $status): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role' => $role->id, 'status'  => $status,
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('status');
    }

    public function test_update_rejects_a_role_that_does_not_exist(): void
    {
        $role = $this->staffRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email'             => $user->email, 'phone' => $user->phone,
                'role' => $this->missingRoleId(), 'status' => 'active',
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('role');
    }

    public function test_update_rejects_a_branch_id_that_does_not_exist(): void
    {
        $role = $this->branchManagerRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone'  => $user->phone,
                'role' => $role->id, 'status'  => 'active', 'branch_ids' => [$this->missingBranchId()],
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('branch_ids.0');
    }

    public function test_update_syncs_branches_for_a_branch_manager_replacing_the_old_set(): void
    {
        $role                = $this->branchManagerRole();
        [$old, $new1, $new2] = Branch::factory()->count(3)->create();
        $user                = User::factory()->create(['role_id' => $role->id]);
        $user->branches()->attach($old);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone'  => $user->phone,
                'role' => $role->id, 'status'  => 'active', 'branch_ids' => [$new1->id, $new2->id],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$new1->id, $new2->id], $this->assignedBranchIds($user));
    }

    public function test_update_removes_all_branches_for_a_branch_manager_when_none_are_submitted(): void
    {
        $role   = $this->branchManagerRole();
        $branch = Branch::factory()->create();
        $user   = User::factory()->create(['role_id' => $role->id]);
        $user->branches()->attach($branch);

        $this->signedIn()->put(route('users.update', $user), [
            'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'role' => $role->id, 'status'  => 'active',
        ]);

        $this->assertSame([], $this->assignedBranchIds($user));
    }

    public function test_update_syncs_stores_for_a_store_manager_replacing_the_old_set(): void
    {
        $role                = $this->storeManagerRole();
        $branch              = Branch::factory()->create();
        [$old, $new1, $new2] = Store::factory()->count(3)->create(['branch_id' => $branch->id]);
        $user                = User::factory()->create(['role_id' => $role->id]);
        $user->stores()->attach($old);

        $this->signedIn()
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role' => $role->id, 'status'  => 'active', 'store_ids' => [$new1->id, $new2->id],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$new1->id, $new2->id], $this->assignedStoreIds($user));
    }

    public function test_update_removes_all_stores_for_a_store_manager_when_none_are_submitted(): void
    {
        $role  = $this->storeManagerRole();
        $store = Store::factory()->create(['branch_id' => Branch::factory()->create()->id]);
        $user  = User::factory()->create(['role_id' => $role->id]);
        $user->stores()->attach($store);

        $this->signedIn()->put(route('users.update', $user), [
            'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
            'role' => $role->id, 'status'  => 'active',
        ]);

        $this->assertSame([], $this->assignedStoreIds($user));
    }

    public function test_update_clears_branch_assignments_when_the_role_changes_away_from_branch_manager(): void
    {
        $managerRole = $this->branchManagerRole();
        $staffRole   = $this->staffRole();
        $branch      = Branch::factory()->create();
        $user        = User::factory()->create(['role_id' => $managerRole->id]);
        $user->branches()->attach($branch);

        // branch_ids is still submitted, but the role is changing away from branch_manager.
        $this->signedIn()->put(route('users.update', $user), [
            'name' => $user->name, 'email'     => $user->email, 'phone'  => $user->phone,
            'role' => $staffRole->id, 'status' => 'active', 'branch_ids' => [$branch->id],
        ]);

        $this->assertSame([], $this->assignedBranchIds($user));
    }

    public function test_update_clears_store_assignments_when_the_role_changes_away_from_store_manager(): void
    {
        $managerRole = $this->storeManagerRole();
        $staffRole   = $this->staffRole();
        $store       = Store::factory()->create(['branch_id' => Branch::factory()->create()->id]);
        $user        = User::factory()->create(['role_id' => $managerRole->id]);
        $user->stores()->attach($store);

        $this->signedIn()->put(route('users.update', $user), [
            'name' => $user->name, 'email'     => $user->email, 'phone' => $user->phone,
            'role' => $staffRole->id, 'status' => 'active', 'store_ids' => [$store->id],
        ]);

        $this->assertSame([], $this->assignedStoreIds($user));
    }

    public function test_update_ignores_branch_and_store_ids_for_a_role_that_is_neither_manager_type(): void
    {
        $role   = $this->staffRole();
        $branch = Branch::factory()->create();
        $store  = Store::factory()->create(['branch_id' => $branch->id]);
        $user   = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()->put(route('users.update', $user), [
            'name'       => $user->name, 'email'       => $user->email, 'phone' => $user->phone,
            'role'       => $role->id, 'status'        => 'active',
            'branch_ids' => [$branch->id], 'store_ids' => [$store->id],
        ]);

        $this->assertSame([], $this->assignedBranchIds($user));
        $this->assertSame([], $this->assignedStoreIds($user));
    }

    public function test_update_rejects_a_store_id_that_does_not_exist(): void
    {
        $role = $this->storeManagerRole();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()
            ->from(route('users.edit', $user))
            ->put(route('users.update', $user), [
                'name' => $user->name, 'email' => $user->email, 'phone' => $user->phone,
                'role' => $role->id, 'status'  => 'active', 'store_ids' => [$this->missingStoreId()],
            ])
            ->assertRedirect(route('users.edit', $user))
            ->assertSessionHasErrors('store_ids.0');
    }

    public function test_update_does_not_affect_other_users_branch_or_store_assignments(): void
    {
        $role      = $this->branchManagerRole();
        $branch    = Branch::factory()->create();
        $bystander = User::factory()->create(['role_id' => $role->id]);
        $bystander->branches()->attach($branch);
        $target = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()->put(route('users.update', $target), [
            'name' => $target->name, 'email' => $target->email, 'phone' => $target->phone,
            'role' => $role->id, 'status'    => 'active',
        ]);

        $this->assertEqualsCanonicalizing([$branch->id], $this->assignedBranchIds($bystander));
    }

    public function test_update_returns_404_for_a_missing_user(): void
    {
        $role = $this->staffRole();

        $this->signedIn()
            ->put(route('users.update', $this->missingUserId()), $this->validPayload($role))
            ->assertNotFound();
    }

    public function test_a_user_can_update_their_own_profile(): void
    {
        $role = $this->staffRole();
        $this->actor->update(['role_id' => $role->id]);

        $this->signedIn()
            ->put(route('users.update', $this->actor), [
                'name' => 'My New Name', 'email' => $this->actor->email, 'phone' => $this->actor->phone,
                'role' => $role->id, 'status'    => 'active',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->actor->id, 'name' => 'My New Name']);
    }

    /* =====================================================================
     | destroy()
     * =================================================================== */

    public function test_destroy_deletes_the_user_and_detaches_branch_and_store_assignments(): void
    {
        $role   = $this->branchManagerRole();
        $branch = Branch::factory()->create();
        $target = User::factory()->create(['role_id' => $role->id]);
        $target->branches()->attach($branch);

        $this->signedIn()
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $target->id]); // A5: assertSoftDeleted if SoftDeletes
        $this->assertDatabaseMissing('branch_user', ['user_id' => $target->id]);
        $this->assertModelExists($branch);
    }

    public function test_destroy_does_not_affect_other_users_or_their_assignments(): void
    {
        $role      = $this->branchManagerRole();
        $branch    = Branch::factory()->create();
        $bystander = User::factory()->create(['role_id' => $role->id]);
        $bystander->branches()->attach($branch);
        $target = User::factory()->create(['role_id' => $role->id]);

        $this->signedIn()->delete(route('users.destroy', $target));

        $this->assertModelExists($bystander);
        $this->assertEqualsCanonicalizing([$branch->id], $this->assignedBranchIds($bystander));
    }

    public function test_destroy_prevents_a_user_from_deleting_their_own_account(): void
    {
        $this->signedIn()
            ->delete(route('users.destroy', $this->actor))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'You cannot delete your own account.')
            ->assertSessionMissing('success');

        $this->assertModelExists($this->actor);
    }

    public function test_destroy_returns_404_for_a_missing_user(): void
    {
        $this->signedIn()->delete(route('users.destroy', $this->missingUserId()))->assertNotFound();
    }
}
