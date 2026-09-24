<?php
namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BranchesControllerTest extends TestCase
{
    use RefreshDatabase;

    // A7
    private const ROLE_STAFF = 2;
    private const ROLE_OTHER = 1;

    // A3 - adjust to match routes/web.php
    private const ROUTE_EDIT_USERS    = 'branches.users.edit';
    private const ROUTE_UPDATE_USERS  = 'branches.users.update';
    private const ROUTE_REMOVE_USER   = 'branches.users.remove';
    private const METHOD_UPDATE_USERS = 'PUT';
    private const METHOD_REMOVE_USER  = 'DELETE';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->actingUser();
    }

    /* =====================================================================
     | Helpers
     * =================================================================== */

    private function actingUser(): User
    {
        return User::factory()->create(); // A9
    }

    private function signedIn(): static
    {
        return $this->actingAs($this->user);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'      => 'Main Branch',
            'code'      => 'MAIN-001',
            'location'  => 'Downtown',
            'is_active' => 1,
        ], $overrides);
    }

    private function makeStore(Branch $branch): Store
    {
        return Store::factory()->create(['branch_id' => $branch->id]); // A1
    }

    private function makeProduct(float $costPrice = 10): Product
    {
        return Product::factory()->create(['cost_price' => $costPrice]); // A1
    }

    private function stock(Store $store, Product $product, int $quantity): void
    {
        DB::table('inventories')->insert([ // A5
            'store_id'   => $store->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
        ]);
    }

    private function recordSale(Store $store, float $total, Carbon $at): void
    {
        DB::table('sales')->insert([ // A5
            'store_id'   => $store->id,
            'total'      => $total,
            'created_at' => $at,
        ]);
    }

    private function assignedUserIds(Branch $branch): array
    {
        return $branch->users()->pluck('users.id')->all();
    }

    private function listedIds(TestResponse $response): array
    {
        return $response->viewData('branches')->pluck('id')->all();
    }

    private function assertViewNumber(float | int $expected, TestResponse $response, string $key): void
    {
        $this->assertEqualsWithDelta(
            $expected,
            (float) $response->viewData($key),
            0.001,
            "View variable [{$key}] has the wrong value."
        );
    }

    private function missingBranchId(): int
    {
        return (int) Branch::max('id') + 1;
    }

    private function missingUserId(): int
    {
        return (int) User::max('id') + 1;
    }

    private function submitUsers(Branch $branch, array $data, ?string $from = null): TestResponse
    {
        $test = $this->signedIn();

        if ($from !== null) {
            $test = $test->from($from);
        }

        return $test->call(
            self::METHOD_UPDATE_USERS,
            route(self::ROUTE_UPDATE_USERS, $branch),
            $data
        );
    }

    /* =====================================================================
     | Data providers
     * =================================================================== */

    public static function requiredFields(): array
    {
        return [
            'name'      => ['name'],
            'code'      => ['code'],
            'location'  => ['location'],
            'is_active' => ['is_active'],
        ];
    }

    public static function invalidFieldValues(): array
    {
        return [
            'name is not a string'   => ['name', ['array']],
            'name too long'          => ['name', str_repeat('a', 256)],
            'code too long'          => ['code', str_repeat('a', 51)],
            'location too long'      => ['location', str_repeat('a', 256)],
            'is_active not boolean'  => ['is_active', 'maybe'],
            'is_active out of range' => ['is_active', 2],
            'phone too long'         => ['phone', str_repeat('1', 21)],
            'email malformed'        => ['email', 'not-an-email'],
            'email too long'         => ['email', str_repeat('a', 250) . '@x.com'],
            'address too long'       => ['address', str_repeat('a', 1001)],
        ];
    }

    public static function maxLengthBoundaries(): array
    {
        return [
            'name 255 chars'     => ['name', 255],
            'code 50 chars'      => ['code', 50],
            'location 255 chars' => ['location', 255],
            'phone 20 chars'     => ['phone', 20],
            'address 1000 chars' => ['address', 1000],
        ];
    }

    public static function searchableColumns(): array
    {
        return [
            'name'     => ['name'],
            'code'     => ['code'],
            'location' => ['location'],
        ];
    }

    public static function invalidUsersPayloads(): array
    {
        return [
            'users is not an array'  => ['not-an-array', 'users'],
            'user id is not integer' => [['abc'], 'users.0'],
            'user id is a decimal'   => [['1.5'], 'users.0'],
        ];
    }

    /* =====================================================================
     | Authentication
     * =================================================================== */

    public function test_guests_are_redirected_to_login_for_every_action(): void
    {
        $branch = Branch::factory()->create();
        $user   = User::factory()->create();

        $requests = [
            ['GET', route('branches.index')],
            ['GET', route('branches.create')],
            ['POST', route('branches.store')],
            ['GET', route('branches.show', $branch)],
            ['GET', route('branches.edit', $branch)],
            ['PUT', route('branches.update', $branch)],
            ['DELETE', route('branches.destroy', $branch)],
            ['GET', route(self::ROUTE_EDIT_USERS, $branch)],
            [self::METHOD_UPDATE_USERS, route(self::ROUTE_UPDATE_USERS, $branch)],
            [self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$branch, $user])],
        ];

        foreach ($requests as [$method, $url]) {
            $response = $this->call($method, $url, $method === 'POST' ? $this->validPayload() : []);

            $this->assertTrue(
                $response->isRedirect(route('login')),
                "Guest was not redirected to login for {$method} {$url} (status {$response->getStatusCode()})."
            );
        }

        // Nothing was created or destroyed by the guest requests.
        $this->assertModelExists($branch);
        $this->assertSame(1, Branch::count());
        $this->assertDatabaseMissing('branches', ['code' => 'MAIN-001']);
    }

    /* =====================================================================
     | index()
     * =================================================================== */

    public function test_index_lists_branches_alphabetically_with_user_counts(): void
    {
        $zulu  = Branch::factory()->create(['name' => 'Zulu Branch']);
        $alpha = Branch::factory()->create(['name' => 'Alpha Branch']);
        $zulu->users()->attach(User::factory()->count(2)->create());

        $response = $this->signedIn()
            ->get(route('branches.index'))
            ->assertOk()
            ->assertViewIs('branches.index')
            ->assertViewHas('branches');

        $branches = $response->viewData('branches');

        $this->assertSame(['Alpha Branch', 'Zulu Branch'], $branches->pluck('name')->all());
        $this->assertSame([0, 2], $branches->pluck('users_count')->map(fn($c) => (int) $c)->all());
    }

    #[DataProvider('searchableColumns')]
    public function test_index_search_matches_name_code_and_location(string $column): void
    {
        $match = Branch::factory()->create([$column => 'Findable Term']);
        Branch::factory()->create();

        $response = $this->signedIn()
            ->get(route('branches.index', ['search' => 'Findable']))
            ->assertOk();

        $this->assertEqualsCanonicalizing([$match->id], $this->listedIds($response));
    }

    public function test_index_search_with_no_matches_returns_empty_list(): void
    {
        Branch::factory()->count(2)->create();

        $response = $this->signedIn()
            ->get(route('branches.index', ['search' => 'no-such-branch-anywhere']))
            ->assertOk();

        $this->assertSame([], $this->listedIds($response));
    }

    public function test_index_blank_search_is_ignored(): void
    {
        $branches = Branch::factory()->count(3)->create();

        $response = $this->signedIn()
            ->get(route('branches.index', ['search' => '']))
            ->assertOk();

        $this->assertEqualsCanonicalizing($branches->pluck('id')->all(), $this->listedIds($response));
    }

    public function test_index_filters_by_active_status(): void
    {
        $active   = Branch::factory()->create(['is_active' => true]);
        $inactive = Branch::factory()->create(['is_active' => false]);

        $response = $this->signedIn()->get(route('branches.index', ['status' => 'active']))->assertOk();

        $this->assertEqualsCanonicalizing([$active->id], $this->listedIds($response));
    }

    public function test_index_filters_by_inactive_status(): void
    {
        Branch::factory()->create(['is_active' => true]);
        $inactive = Branch::factory()->create(['is_active' => false]);

        $response = $this->signedIn()->get(route('branches.index', ['status' => 'inactive']))->assertOk();

        $this->assertEqualsCanonicalizing([$inactive->id], $this->listedIds($response));
    }

    public function test_index_without_status_returns_active_and_inactive_branches(): void
    {
        $active   = Branch::factory()->create(['is_active' => true]);
        $inactive = Branch::factory()->create(['is_active' => false]);

        $response = $this->signedIn()->get(route('branches.index'))->assertOk();

        $this->assertEqualsCanonicalizing([$active->id, $inactive->id], $this->listedIds($response));
    }

    /**
     * Documents CURRENT behaviour: any status value other than "active"
     * (including garbage) is treated as "inactive". See notes in the write-up.
     */
    public function test_index_treats_unrecognised_status_as_inactive(): void
    {
        Branch::factory()->create(['is_active' => true]);
        $inactive = Branch::factory()->create(['is_active' => false]);

        $response = $this->signedIn()->get(route('branches.index', ['status' => 'garbage']))->assertOk();

        $this->assertEqualsCanonicalizing([$inactive->id], $this->listedIds($response));
    }

    public function test_index_combines_search_and_status_filters(): void
    {
        $match = Branch::factory()->create(['name' => 'Coastal HQ', 'is_active' => true]);
        Branch::factory()->create(['name' => 'Coastal Depot', 'is_active' => false]);
        Branch::factory()->create(['name' => 'Inland HQ', 'is_active' => true]);

        $response = $this->signedIn()
            ->get(route('branches.index', ['search' => 'Coastal', 'status' => 'active']))
            ->assertOk();

        $this->assertEqualsCanonicalizing([$match->id], $this->listedIds($response));
    }

    public function test_index_paginates_fifteen_per_page_and_keeps_query_string(): void
    {
        foreach (range(1, 16) as $i) {
            Branch::factory()->create(['name' => sprintf('Bulk %02d', $i)]);
        }
        Branch::factory()->create(['name' => 'Something else']);

        $page1 = $this->signedIn()
            ->get(route('branches.index', ['search' => 'Bulk']))
            ->assertOk()
            ->viewData('branches');

        $this->assertCount(15, $page1);
        $this->assertSame(16, $page1->total());
        $this->assertTrue($page1->hasMorePages());
        $this->assertStringContainsString('search=Bulk', $page1->nextPageUrl());

        $page2 = $this->signedIn()
            ->get(route('branches.index', ['search' => 'Bulk', 'page' => 2]))
            ->assertOk()
            ->viewData('branches');

        $this->assertSame(['Bulk 16'], $page2->pluck('name')->all());
    }

    /* =====================================================================
     | create()
     * =================================================================== */

    public function test_create_displays_the_form(): void
    {
        $this->signedIn()
            ->get(route('branches.create'))
            ->assertOk()
            ->assertViewIs('branches.create');
    }

    /* =====================================================================
     | store()
     * =================================================================== */

    public function test_store_creates_a_branch_with_all_fields(): void
    {
        $before  = Branch::count();
        $payload = $this->validPayload([
            'phone'   => '+254700000000',
            'email'   => 'main@example.com',
            'address' => '1 Main Street',
        ]);

        $this->signedIn()
            ->post(route('branches.store'), $payload)
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('success', 'Branch created successfully.')
            ->assertSessionHasNoErrors();

        $this->assertSame($before + 1, Branch::count());
        $this->assertDatabaseHas('branches', [
            'name'      => 'Main Branch',
            'code'      => 'MAIN-001',
            'location'  => 'Downtown',
            'is_active' => true,
            'phone'     => '+254700000000',
            'email'     => 'main@example.com',
            'address'   => '1 Main Street',
        ]);
    }

    public function test_store_creates_a_branch_with_only_required_fields(): void
    {
        $this->signedIn()
            ->post(route('branches.store'), $this->validPayload())
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', [
            'code'    => 'MAIN-001',
            'phone'   => null,
            'email'   => null,
            'address' => null,
        ]);
    }

    public function test_store_persists_inactive_branches(): void
    {
        $this->signedIn()
            ->post(route('branches.store'), $this->validPayload(['is_active' => 0]))
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', ['code' => 'MAIN-001', 'is_active' => false]);
    }

    public function test_store_treats_blank_optional_fields_as_null(): void
    {
        $this->signedIn()
            ->post(route('branches.store'), $this->validPayload(['phone' => '', 'email' => '', 'address' => '']))
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', [
            'code'    => 'MAIN-001',
            'phone'   => null,
            'email'   => null,
            'address' => null,
        ]);
    }

    #[DataProvider('requiredFields')]
    public function test_store_requires_field_when_missing(string $field): void
    {
        $before  = Branch::count();
        $payload = $this->validPayload();
        unset($payload[$field]);

        $this->signedIn()
            ->from(route('branches.create'))
            ->post(route('branches.store'), $payload)
            ->assertRedirect(route('branches.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Branch::count());
    }

    #[DataProvider('requiredFields')]
    public function test_store_requires_field_when_blank(string $field): void
    {
        $before = Branch::count();

        $this->signedIn()
            ->from(route('branches.create'))
            ->post(route('branches.store'), $this->validPayload([$field => '']))
            ->assertRedirect(route('branches.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Branch::count());
    }

    #[DataProvider('invalidFieldValues')]
    public function test_store_rejects_invalid_values(string $field, mixed $value): void
    {
        $before = Branch::count();

        $this->signedIn()
            ->from(route('branches.create'))
            ->post(route('branches.store'), $this->validPayload([$field => $value]))
            ->assertRedirect(route('branches.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Branch::count());
    }

    #[DataProvider('maxLengthBoundaries')]
    public function test_store_accepts_values_exactly_at_the_maximum_length(string $field, int $length): void
    {
        $value = str_repeat('a', $length);

        $this->signedIn()
            ->post(route('branches.store'), $this->validPayload([$field => $value]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('branches.index'));

        // Also proves the DB column is at least as wide as the validation rule.
        $this->assertDatabaseHas('branches', [$field => $value]);
    }

    public function test_store_rejects_a_duplicate_code(): void
    {
        Branch::factory()->create(['code' => 'MAIN-001']);
        $before = Branch::count();

        $this->signedIn()
            ->from(route('branches.create'))
            ->post(route('branches.store'), $this->validPayload(['code' => 'MAIN-001', 'name' => 'Duplicate']))
            ->assertRedirect(route('branches.create'))
            ->assertSessionHasErrors('code');

        $this->assertSame($before, Branch::count());
        $this->assertDatabaseMissing('branches', ['name' => 'Duplicate']);
    }

    /* =====================================================================
     | show()
     * =================================================================== */

    public function test_show_displays_the_branch_with_users_and_stores_loaded(): void
    {
        $branch = Branch::factory()->create();
        $branch->users()->attach(User::factory()->create());
        $this->makeStore($branch);

        $response = $this->signedIn()
            ->get(route('branches.show', $branch))
            ->assertOk()
            ->assertViewIs('branches.show')
            ->assertViewHasAll(['branch', 'usersCount', 'storesCount', 'productsCount', 'inventoryValue', 'salesToday']);

        $shown = $response->viewData('branch');
        $this->assertTrue($shown->is($branch));
        $this->assertTrue($shown->relationLoaded('users'));
        $this->assertTrue($shown->relationLoaded('stores'));
    }

    public function test_show_returns_zero_figures_for_a_branch_with_no_users_stores_or_stock(): void
    {
        $branch = Branch::factory()->create();

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(0, $response, 'usersCount');
        $this->assertViewNumber(0, $response, 'storesCount');
        $this->assertViewNumber(0, $response, 'productsCount');
        $this->assertViewNumber(0, $response, 'inventoryValue');
        $this->assertViewNumber(0, $response, 'salesToday');
    }

    public function test_show_counts_only_this_branchs_users_and_stores(): void
    {
        $branch = Branch::factory()->create();
        $other  = Branch::factory()->create();

        $branch->users()->attach(User::factory()->count(3)->create());
        $other->users()->attach(User::factory()->count(2)->create());
        $this->makeStore($branch);
        $this->makeStore($branch);
        $this->makeStore($other);

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(3, $response, 'usersCount');
        $this->assertViewNumber(2, $response, 'storesCount');
    }

    public function test_show_counts_distinct_products_across_all_branch_stores(): void
    {
        $branch     = Branch::factory()->create();
        $other      = Branch::factory()->create();
        $storeA     = $this->makeStore($branch);
        $storeB     = $this->makeStore($branch);
        $otherStore = $this->makeStore($other);

        [$p1, $p2, $p3, $p4] = [
            $this->makeProduct(), $this->makeProduct(), $this->makeProduct(), $this->makeProduct(),
        ];

        $this->stock($storeA, $p1, 5);
        $this->stock($storeA, $p2, 5);
        $this->stock($storeB, $p1, 5); // same product in a second store: counted once
        $this->stock($storeB, $p3, 5);
        $this->stock($otherStore, $p4, 5); // other branch: excluded

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(3, $response, 'productsCount');
    }

    /**
     * Documents CURRENT behaviour: an inventory row with quantity 0 still
     * counts as a "stocked" product. See notes in the write-up.
     */
    public function test_show_counts_inventory_rows_with_zero_quantity_as_stocked_products(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);

        $this->stock($store, $this->makeProduct(), 0);

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(1, $response, 'productsCount');
    }

    public function test_show_calculates_inventory_value_from_quantity_times_cost_price(): void
    {
        $branch     = Branch::factory()->create();
        $other      = Branch::factory()->create();
        $storeA     = $this->makeStore($branch);
        $storeB     = $this->makeStore($branch);
        $otherStore = $this->makeStore($other);

        $p1 = $this->makeProduct(12.50);
        $p2 = $this->makeProduct(30.00);

        $this->stock($storeA, $p1, 10);       // 125.00
        $this->stock($storeA, $p2, 4);        // 120.00
        $this->stock($storeB, $p1, 2);        //  25.00
        $this->stock($otherStore, $p1, 1000); // other branch: excluded

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(270.00, $response, 'inventoryValue');
    }

    public function test_show_calculates_sales_today_for_this_branch_only(): void
    {
        $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

        $branch     = Branch::factory()->create();
        $other      = Branch::factory()->create();
        $storeA     = $this->makeStore($branch);
        $storeB     = $this->makeStore($branch);
        $otherStore = $this->makeStore($other);

        $this->recordSale($storeA, 100.50, Carbon::parse('2026-03-15 10:00:00'));
        $this->recordSale($storeB, 200.00, Carbon::parse('2026-03-15 15:30:00'));
        $this->recordSale($otherStore, 5000.00, Carbon::parse('2026-03-15 11:00:00')); // other branch

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(300.50, $response, 'salesToday');
    }

    public function test_show_sales_today_includes_day_boundaries_and_excludes_other_days(): void
    {
        $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);

        $this->recordSale($store, 999.00, Carbon::parse('2026-03-14 23:59:59')); // yesterday
        $this->recordSale($store, 10.00, Carbon::parse('2026-03-15 00:00:00'));  // first second of today
        $this->recordSale($store, 20.00, Carbon::parse('2026-03-15 23:59:59'));  // last second of today
        $this->recordSale($store, 999.00, Carbon::parse('2026-03-16 00:00:00')); // tomorrow

        $response = $this->signedIn()->get(route('branches.show', $branch))->assertOk();

        $this->assertViewNumber(30.00, $response, 'salesToday');
    }

    public function test_show_returns_404_for_a_missing_branch(): void
    {
        $this->signedIn()
            ->get(route('branches.show', $this->missingBranchId()))
            ->assertNotFound();
    }

    /* =====================================================================
     | edit()
     * =================================================================== */

    public function test_edit_displays_the_form_for_the_branch(): void
    {
        $branch = Branch::factory()->create();

        $this->signedIn()
            ->get(route('branches.edit', $branch))
            ->assertOk()
            ->assertViewIs('branches.edit')
            ->assertViewHas('branch', fn($shown) => $shown->is($branch));
    }

    public function test_edit_returns_404_for_a_missing_branch(): void
    {
        $this->signedIn()
            ->get(route('branches.edit', $this->missingBranchId()))
            ->assertNotFound();
    }

    /* =====================================================================
     | update()
     * =================================================================== */

    public function test_update_changes_all_fields_and_leaves_other_branches_alone(): void
    {
        $branch = Branch::factory()->create([
            'name'  => 'Old Name', 'code'    => 'OLD-1', 'location'          => 'Old Location', 'is_active' => true,
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old Address',
        ]);
        $other = Branch::factory()->create(['name' => 'Untouched']);

        $payload = [
            'name'  => 'New Name', 'code'    => 'NEW-1', 'location'          => 'New Location', 'is_active' => 0,
            'phone' => '0711111111', 'email' => 'new@example.com', 'address' => 'New Address',
        ];

        $this->signedIn()
            ->put(route('branches.update', $branch), $payload)
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('success', 'Branch updated successfully.')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('branches', array_merge($payload, ['id' => $branch->id, 'is_active' => false]));
        $this->assertDatabaseMissing('branches', ['code' => 'OLD-1']);
        $this->assertDatabaseHas('branches', ['id' => $other->id, 'name' => 'Untouched']);
    }

    public function test_update_allows_keeping_the_branchs_own_code(): void
    {
        $branch = Branch::factory()->create(['code' => 'KEEP-1']);

        $this->signedIn()
            ->put(route('branches.update', $branch), $this->validPayload(['code' => 'KEEP-1', 'name' => 'Renamed']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'code' => 'KEEP-1', 'name' => 'Renamed']);
    }

    public function test_update_rejects_a_code_used_by_another_branch(): void
    {
        Branch::factory()->create(['code' => 'TAKEN-1']);
        $branch = Branch::factory()->create(['code' => 'MINE-1', 'name' => 'Original']);

        $this->signedIn()
            ->from(route('branches.edit', $branch))
            ->put(route('branches.update', $branch), $this->validPayload(['code' => 'TAKEN-1', 'name' => 'Changed']))
            ->assertRedirect(route('branches.edit', $branch))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'code' => 'MINE-1', 'name' => 'Original']);
    }

    public function test_update_clears_optional_fields_when_sent_blank(): void
    {
        $branch = Branch::factory()->create([
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old Address',
        ]);

        $this->signedIn()
            ->put(route('branches.update', $branch), $this->validPayload(['phone' => '', 'email' => '', 'address' => '']))
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'phone' => null, 'email' => null, 'address' => null]);
    }

    public function test_update_keeps_optional_fields_that_are_not_submitted(): void
    {
        $branch = Branch::factory()->create([
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old Address',
        ]);

        $this->signedIn()
            ->put(route('branches.update', $branch), $this->validPayload(['name' => 'Only Name Changed']))
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', [
            'id'    => $branch->id, 'name'   => 'Only Name Changed',
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old Address',
        ]);
    }

    public function test_update_does_not_touch_the_branchs_users_or_stores(): void
    {
        $branch = Branch::factory()->create();
        $user   = User::factory()->create();
        $store  = $this->makeStore($branch);
        $branch->users()->attach($user);

        $this->signedIn()
            ->put(route('branches.update', $branch), $this->validPayload(['code' => 'CHG-1']))
            ->assertRedirect(route('branches.index'));

        $this->assertEqualsCanonicalizing([$user->id], $this->assignedUserIds($branch));
        $this->assertModelExists($store);
    }

    #[DataProvider('requiredFields')]
    public function test_update_requires_field_when_missing(string $field): void
    {
        $branch  = Branch::factory()->create(['name' => 'Original']);
        $payload = $this->validPayload(['code' => 'UPD-1']);
        unset($payload[$field]);

        $this->signedIn()
            ->from(route('branches.edit', $branch))
            ->put(route('branches.update', $branch), $payload)
            ->assertRedirect(route('branches.edit', $branch))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Original']);
    }

    #[DataProvider('invalidFieldValues')]
    public function test_update_rejects_invalid_values(string $field, mixed $value): void
    {
        $branch = Branch::factory()->create(['name' => 'Original']);

        $this->signedIn()
            ->from(route('branches.edit', $branch))
            ->put(route('branches.update', $branch), $this->validPayload(['code' => 'UPD-1', $field => $value]))
            ->assertRedirect(route('branches.edit', $branch))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas('branches', ['id' => $branch->id, 'name' => 'Original']);
    }

    #[DataProvider('maxLengthBoundaries')]
    public function test_update_accepts_values_exactly_at_the_maximum_length(string $field, int $length): void
    {
        $branch = Branch::factory()->create();
        $value  = str_repeat('a', $length);

        $this->signedIn()
            ->put(route('branches.update', $branch), $this->validPayload(['code' => 'UPD-1', $field => $value]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', ['id' => $branch->id, $field => $value]);
    }

    public function test_update_returns_404_for_a_missing_branch(): void
    {
        $before = Branch::count();

        $this->signedIn()
            ->put(route('branches.update', $this->missingBranchId()), $this->validPayload())
            ->assertNotFound();

        $this->assertSame($before, Branch::count());
    }

    /* =====================================================================
     | destroy()
     * =================================================================== */

    public function test_destroy_deletes_a_branch_without_stores_or_users(): void
    {
        $branch = Branch::factory()->create();
        $other  = Branch::factory()->create();

        $this->signedIn()
            ->delete(route('branches.destroy', $branch))
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('success', 'Branch deleted successfully.');

        $this->assertDatabaseMissing('branches', ['id' => $branch->id]); // A6: swap for assertSoftDeleted if SoftDeletes
        $this->assertModelExists($other);
    }

    public function test_destroy_is_blocked_when_the_branch_has_stores(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);

        $this->signedIn()
            ->delete(route('branches.destroy', $branch))
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('error', 'Cannot delete this branch because it has stores.')
            ->assertSessionMissing('success');

        $this->assertModelExists($branch);
        $this->assertModelExists($store);
    }

    public function test_destroy_is_blocked_when_users_are_assigned(): void
    {
        $branch = Branch::factory()->create();
        $user   = User::factory()->create();
        $branch->users()->attach($user);

        $this->signedIn()
            ->delete(route('branches.destroy', $branch))
            ->assertRedirect(route('branches.index'))
            ->assertSessionHas('error', 'Cannot delete this branch because users are assigned to it.')
            ->assertSessionMissing('success');

        $this->assertModelExists($branch);
        $this->assertEqualsCanonicalizing([$user->id], $this->assignedUserIds($branch));
    }

    public function test_destroy_reports_the_stores_error_first_when_both_stores_and_users_exist(): void
    {
        $branch = Branch::factory()->create();
        $this->makeStore($branch);
        $branch->users()->attach(User::factory()->create());

        $this->signedIn()
            ->delete(route('branches.destroy', $branch))
            ->assertSessionHas('error', 'Cannot delete this branch because it has stores.');

        $this->assertModelExists($branch);
    }

    public function test_destroy_does_not_delete_user_accounts_of_other_branches(): void
    {
        $branch = Branch::factory()->create();
        $other  = Branch::factory()->create();
        $user   = User::factory()->create();
        $other->users()->attach($user);

        $this->signedIn()->delete(route('branches.destroy', $branch));

        $this->assertModelExists($user);
        $this->assertEqualsCanonicalizing([$user->id], $this->assignedUserIds($other));
    }

    public function test_destroy_returns_404_for_a_missing_branch(): void
    {
        $this->signedIn()
            ->delete(route('branches.destroy', $this->missingBranchId()))
            ->assertNotFound();
    }

    /* =====================================================================
     | editUser()
     * =================================================================== */

    public function test_edit_users_lists_only_role_2_users(): void
    {
        $branch = Branch::factory()->create();
        $staffA = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $staffB = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $other  = User::factory()->create(['role_id' => self::ROLE_OTHER]);

        $response = $this->signedIn()
            ->get(route(self::ROUTE_EDIT_USERS, $branch))
            ->assertOk()
            ->assertViewIs('branches.edit-users')
            ->assertViewHas('branch', fn($shown) => $shown->is($branch));

        $users = $response->viewData('users');

        $this->assertTrue($users->contains('id', $staffA->id));
        $this->assertTrue($users->contains('id', $staffB->id));
        $this->assertFalse($users->contains('id', $other->id));
        $this->assertTrue($users->every(fn($u) => (int) $u->role_id === self::ROLE_STAFF));
    }

    /**
     * Documents CURRENT behaviour: the candidate list is not limited to users
     * who are unassigned, so staff already assigned elsewhere are offered too.
     */
    public function test_edit_users_also_lists_role_2_users_assigned_to_other_branches(): void
    {
        $branch = Branch::factory()->create();
        $other  = Branch::factory()->create();
        $staff  = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $other->users()->attach($staff);

        $users = $this->signedIn()
            ->get(route(self::ROUTE_EDIT_USERS, $branch))
            ->assertOk()
            ->viewData('users');

        $this->assertTrue($users->contains('id', $staff->id));
    }

    public function test_edit_users_returns_404_for_a_missing_branch(): void
    {
        $this->signedIn()
            ->get(route(self::ROUTE_EDIT_USERS, $this->missingBranchId()))
            ->assertNotFound();
    }

    /* =====================================================================
     | updateUser()
     * =================================================================== */

    public function test_update_users_assigns_the_selected_users(): void
    {
        $branch  = Branch::factory()->create();
        [$a, $b] = User::factory()->count(2)->create(['role_id' => self::ROLE_STAFF])->all();

        $this->submitUsers($branch, ['users' => [$a->id, $b->id]])
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHas('success', 'Branch users updated successfully.')
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $this->assignedUserIds($branch));
    }

    public function test_update_users_removes_unselected_and_adds_new_ones(): void
    {
        $branch              = Branch::factory()->create();
        [$keep, $drop, $add] = User::factory()->count(3)->create(['role_id' => self::ROLE_STAFF])->all();
        $branch->users()->attach([$keep->id, $drop->id]);

        $this->submitUsers($branch, ['users' => [$keep->id, $add->id]])
            ->assertRedirect(route('branches.show', $branch));

        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $this->assignedUserIds($branch));
        $this->assertModelExists($drop); // detached, not deleted
    }

    public function test_update_users_does_not_duplicate_an_already_assigned_user(): void
    {
        $branch = Branch::factory()->create();
        $user   = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($user);

        $this->submitUsers($branch, ['users' => [$user->id]])->assertRedirect(route('branches.show', $branch));

        $this->assertSame(1, $branch->users()->count());
    }

    public function test_update_users_with_an_empty_array_detaches_everyone(): void
    {
        $branch = Branch::factory()->create();
        $users  = User::factory()->count(2)->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($users);

        $this->submitUsers($branch, ['users' => []])
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHas('success', 'Branch users updated successfully.');

        $this->assertSame([], $this->assignedUserIds($branch));
        $users->each(fn($u) => $this->assertModelExists($u));
    }

    public function test_update_users_with_users_omitted_detaches_everyone(): void
    {
        $branch = Branch::factory()->create();
        $branch->users()->attach(User::factory()->create(['role_id' => self::ROLE_STAFF]));

        $this->submitUsers($branch, [])
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHasNoErrors();

        $this->assertSame([], $this->assignedUserIds($branch));
    }

    public function test_update_users_does_not_change_other_branches_assignments(): void
    {
        $branch = Branch::factory()->create();
        $other  = Branch::factory()->create();
        $shared = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $newOne = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $other->users()->attach($shared);

        $this->submitUsers($branch, ['users' => [$newOne->id]]);

        $this->assertEqualsCanonicalizing([$shared->id], $this->assignedUserIds($other));
        $this->assertEqualsCanonicalizing([$newOne->id], $this->assignedUserIds($branch));
    }

    #[DataProvider('invalidUsersPayloads')]
    public function test_update_users_rejects_malformed_input(mixed $users, string $errorKey): void
    {
        $branch   = Branch::factory()->create();
        $existing = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $branch);

        $this->submitUsers($branch, ['users' => $users], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors($errorKey);

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($branch));
    }

    public function test_update_users_rejects_duplicate_user_ids(): void
    {
        $branch   = Branch::factory()->create();
        $existing = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $user     = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $branch);

        $this->submitUsers($branch, ['users' => [$user->id, $user->id]], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors('users.0');

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($branch));
    }

    public function test_update_users_rejects_a_user_id_that_does_not_exist(): void
    {
        $branch   = Branch::factory()->create();
        $existing = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $branch);

        $this->submitUsers($branch, ['users' => [$existing->id, $this->missingUserId()]], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors('users.1');

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($branch));
    }

    /**
     * Documents CURRENT behaviour: validation only checks that the user exists,
     * NOT that they are role 2, so a crafted request can assign anyone.
     */
    public function test_update_users_currently_allows_assigning_a_user_who_is_not_role_2(): void
    {
        $branch = Branch::factory()->create();
        $other  = User::factory()->create(['role_id' => self::ROLE_OTHER]);

        $this->submitUsers($branch, ['users' => [$other->id]])
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$other->id], $this->assignedUserIds($branch));
    }

    public function test_update_users_returns_404_for_a_missing_branch(): void
    {
        $user = User::factory()->create(['role_id' => self::ROLE_STAFF]);

        $this->signedIn()
            ->call(self::METHOD_UPDATE_USERS, route(self::ROUTE_UPDATE_USERS, $this->missingBranchId()), ['users' => [$user->id]])
            ->assertNotFound();
    }

    /* =====================================================================
     | removeUser()
     * =================================================================== */

    public function test_remove_user_detaches_only_that_user_from_this_branch(): void
    {
        $branch            = Branch::factory()->create();
        $other             = Branch::factory()->create();
        [$removed, $stays] = User::factory()->count(2)->create(['role_id' => self::ROLE_STAFF])->all();

        $branch->users()->attach([$removed->id, $stays->id]);
        $other->users()->attach($removed);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$branch, $removed]))
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHas('success', 'User removed from branch successfully.');

        $this->assertEqualsCanonicalizing([$stays->id], $this->assignedUserIds($branch));
        $this->assertEqualsCanonicalizing([$removed->id], $this->assignedUserIds($other)); // other branch untouched
        $this->assertModelExists($removed);                                                // account itself is not deleted
    }

    public function test_remove_user_who_is_not_in_the_branch_still_succeeds_without_side_effects(): void
    {
        $branch   = Branch::factory()->create();
        $member   = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $stranger = User::factory()->create(['role_id' => self::ROLE_STAFF]);
        $branch->users()->attach($member);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$branch, $stranger]))
            ->assertRedirect(route('branches.show', $branch))
            ->assertSessionHas('success', 'User removed from branch successfully.');

        $this->assertEqualsCanonicalizing([$member->id], $this->assignedUserIds($branch));
    }

    public function test_remove_user_returns_404_for_a_missing_branch(): void
    {
        $user = User::factory()->create();

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$this->missingBranchId(), $user->id]))
            ->assertNotFound();
    }

    public function test_remove_user_returns_404_for_a_missing_user(): void
    {
        $branch = Branch::factory()->create();
        $member = User::factory()->create();
        $branch->users()->attach($member);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$branch, $this->missingUserId()]))
            ->assertNotFound();

        $this->assertEqualsCanonicalizing([$member->id], $this->assignedUserIds($branch));
    }
}
