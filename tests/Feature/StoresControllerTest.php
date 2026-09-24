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

class StoresControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ROLE_STORE_STAFF = 3;
    private const ROLE_OTHER       = 2;

    // A3
    private const ROUTE_EDIT_USERS    = 'stores.users.edit';
    private const ROUTE_UPDATE_USERS  = 'stores.users.update';
    private const ROUTE_REMOVE_USER   = 'stores.users.remove';
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

    private function stock(Store $store, Product $product, int $quantity, float $costPrice = 0): void
    {
        $row = [ // A5
            'store_id'   => $store->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
        ];

        DB::table('inventories')->insert($row);
    }

    private function makeStore(?Branch $branch = null, array $attributes = []): Store
    {
        $branch ??= Branch::factory()->create();

        return Store::factory()->create(array_merge(['branch_id' => $branch->id], $attributes));
    }

    private function validPayload(Branch $branch, array $overrides = []): array
    {
        return array_merge([
            'name'      => 'Central Store',
            'code'      => 'STR-001',
            'branch_id' => $branch->id,
            'is_active' => 1,
            'location'  => 'Downtown',
        ], $overrides);
    }

    private function recordSale(Store $store, float $total, string $at): void
    {
        DB::table('sales')->insert([ // A5
            'store_id'   => $store->id,
            'total'      => $total,
            'created_at' => Carbon::parse($at),
        ]);
    }

    private function assignedUserIds(Store $store): array
    {
        return $store->users()->pluck('users.id')->all();
    }

    private function listedIds(TestResponse $response): array
    {
        return $response->viewData('stores')->pluck('id')->all();
    }

    private function assertNumber(float | int $expected, mixed $actual, string $label): void
    {
        $this->assertEqualsWithDelta($expected, (float) $actual, 0.001, "{$label} has the wrong value.");
    }

    private function missingStoreId(): int
    {
        return (int) Store::max('id') + 1;
    }

    private function missingBranchId(): int
    {
        return (int) Branch::max('id') + 1;
    }

    private function missingUserId(): int
    {
        return (int) User::max('id') + 1;
    }

    private function submitUsers(Store $store, array $data, ?string $from = null): TestResponse
    {
        $test = $this->signedIn();

        if ($from !== null) {
            $test = $test->from($from);
        }

        return $test->call(self::METHOD_UPDATE_USERS, route(self::ROUTE_UPDATE_USERS, $store), $data);
    }

    /* =====================================================================
     | Data providers
     * =================================================================== */

    public static function requiredFields(): array
    {
        return [
            'name'      => ['name'],
            'code'      => ['code'],
            'branch_id' => ['branch_id'],
            'is_active' => ['is_active'],
            'location'  => ['location'],
        ];
    }

    public static function invalidFieldValues(): array
    {
        return [
            'name is not a string'   => ['name', ['array']],
            'name too long'          => ['name', str_repeat('a', 256)],
            'code too long'          => ['code', str_repeat('a', 51)],
            'branch_id not integer'  => ['branch_id', 'abc'],
            'branch_id decimal'      => ['branch_id', '1.5'],
            'is_active not boolean'  => ['is_active', 'maybe'],
            'is_active out of range' => ['is_active', 2],
            'location too long'      => ['location', str_repeat('a', 256)],
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
            'user id is blank'       => [[''], 'users.0'],
        ];
    }

    /* =====================================================================
     | Authentication
     * =================================================================== */

    public function test_guests_are_redirected_to_login_for_every_action(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $user   = User::factory()->create();

        $requests = [
            ['GET', route('stores.index')],
            ['GET', route('stores.create')],
            ['POST', route('stores.store')],
            ['GET', route('stores.show', $store)],
            ['GET', route('stores.edit', $store)],
            ['PUT', route('stores.update', $store)],
            ['DELETE', route('stores.destroy', $store)],
            ['GET', route(self::ROUTE_EDIT_USERS, $store)],
            [self::METHOD_UPDATE_USERS, route(self::ROUTE_UPDATE_USERS, $store)],
            [self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$store, $user])],
        ];

        foreach ($requests as [$method, $url]) {
            $response = $this->call($method, $url, $method === 'POST' ? $this->validPayload($branch) : []);

            $this->assertTrue(
                $response->isRedirect(route('login')),
                "Guest was not redirected to login for {$method} {$url} (status {$response->getStatusCode()})."
            );
        }

        $this->assertModelExists($store);
        $this->assertSame(1, Store::count());
        $this->assertDatabaseMissing('stores', ['code' => 'STR-001']);
    }

    /* =====================================================================
     | index()
     * =================================================================== */

    public function test_index_lists_newest_stores_first_with_branch_and_user_counts(): void
    {
        $branch = Branch::factory()->create();
        $oldest = $this->makeStore($branch, ['name' => 'Oldest', 'created_at' => now()->subDays(3)]);
        $newest = $this->makeStore($branch, ['name' => 'Newest', 'created_at' => now()->subDay()]);
        $newest->users()->attach(User::factory()->count(2)->create());

        $response = $this->signedIn()
            ->get(route('stores.index'))
            ->assertOk()
            ->assertViewIs('stores.index')
            ->assertViewHasAll(['stores', 'branches']);

        $stores = $response->viewData('stores');

        $this->assertSame([$newest->id, $oldest->id], $stores->pluck('id')->all());
        $this->assertSame([2, 0], $stores->pluck('users_count')->map(fn($c) => (int) $c)->all());
        $this->assertTrue($stores->first()->relationLoaded('branch'));
    }

    public function test_index_passes_all_branches_sorted_by_name_for_the_filter(): void
    {
        Branch::factory()->create(['name' => 'Zulu']);
        Branch::factory()->create(['name' => 'Alpha']);

        $branches = $this->signedIn()->get(route('stores.index'))->assertOk()->viewData('branches');

        $this->assertSame(['Alpha', 'Zulu'], $branches->pluck('name')->all());
    }

    #[DataProvider('searchableColumns')]
    public function test_index_search_matches_name_code_and_location(string $column): void
    {
        $match = $this->makeStore(null, [$column => 'Findable Term']);
        $this->makeStore();

        $response = $this->signedIn()->get(route('stores.index', ['search' => 'Findable']))->assertOk();

        $this->assertEqualsCanonicalizing([$match->id], $this->listedIds($response));
    }

    public function test_index_blank_filters_are_ignored(): void
    {
        $stores = collect([$this->makeStore(), $this->makeStore()]);

        $response = $this->signedIn()
            ->get(route('stores.index', ['search' => '', 'branch_id' => '', 'status' => '']))
            ->assertOk();

        $this->assertEqualsCanonicalizing($stores->pluck('id')->all(), $this->listedIds($response));
    }

    public function test_index_filters_by_branch(): void
    {
        $branchA = Branch::factory()->create();
        $branchB = Branch::factory()->create();
        $inA     = $this->makeStore($branchA);
        $this->makeStore($branchB);

        $response = $this->signedIn()->get(route('stores.index', ['branch_id' => $branchA->id]))->assertOk();

        $this->assertEqualsCanonicalizing([$inA->id], $this->listedIds($response));
    }

    public function test_index_branch_filter_with_unknown_branch_returns_nothing(): void
    {
        $this->makeStore();

        $response = $this->signedIn()
            ->get(route('stores.index', ['branch_id' => $this->missingBranchId()]))
            ->assertOk();

        $this->assertSame([], $this->listedIds($response));
    }

    public function test_index_filters_by_active_and_inactive_status(): void
    {
        $active   = $this->makeStore(null, ['is_active' => true]);
        $inactive = $this->makeStore(null, ['is_active' => false]);

        $activeResponse   = $this->signedIn()->get(route('stores.index', ['status' => 'active']))->assertOk();
        $inactiveResponse = $this->signedIn()->get(route('stores.index', ['status' => 'inactive']))->assertOk();

        $this->assertEqualsCanonicalizing([$active->id], $this->listedIds($activeResponse));
        $this->assertEqualsCanonicalizing([$inactive->id], $this->listedIds($inactiveResponse));
    }

    /** Documents CURRENT behaviour: any status other than "active" means inactive. */
    public function test_index_treats_unrecognised_status_as_inactive(): void
    {
        $this->makeStore(null, ['is_active' => true]);
        $inactive = $this->makeStore(null, ['is_active' => false]);

        $response = $this->signedIn()->get(route('stores.index', ['status' => 'garbage']))->assertOk();

        $this->assertEqualsCanonicalizing([$inactive->id], $this->listedIds($response));
    }

    public function test_index_combines_search_branch_and_status_filters(): void
    {
        $branch = Branch::factory()->create();
        $match  = $this->makeStore($branch, ['name' => 'Coastal One', 'is_active' => true]);
        $this->makeStore($branch, ['name' => 'Coastal Two', 'is_active' => false]);
        $this->makeStore($branch, ['name' => 'Inland One', 'is_active' => true]);
        $this->makeStore(null, ['name' => 'Coastal Three', 'is_active' => true]); // other branch

        $response = $this->signedIn()
            ->get(route('stores.index', ['search' => 'Coastal', 'branch_id' => $branch->id, 'status' => 'active']))
            ->assertOk();

        $this->assertEqualsCanonicalizing([$match->id], $this->listedIds($response));
    }

    public function test_index_paginates_fifteen_per_page_and_keeps_query_string(): void
    {
        $branch = Branch::factory()->create();

        foreach (range(1, 16) as $i) {
            $this->makeStore($branch, [
                'name'       => sprintf('Bulk %02d', $i),
                'created_at' => now()->subMinutes(20 - $i), // Bulk 16 newest, Bulk 01 oldest
            ]);
        }

        $page1 = $this->signedIn()
            ->get(route('stores.index', ['search' => 'Bulk']))
            ->assertOk()
            ->viewData('stores');

        $this->assertCount(15, $page1);
        $this->assertSame(16, $page1->total());
        $this->assertStringContainsString('search=Bulk', $page1->nextPageUrl());

        $page2 = $this->signedIn()
            ->get(route('stores.index', ['search' => 'Bulk', 'page' => 2]))
            ->assertOk()
            ->viewData('stores');

        $this->assertSame(['Bulk 01'], $page2->pluck('name')->all());
    }

    /* =====================================================================
     | create()
     * =================================================================== */

    public function test_create_displays_the_form_with_all_branches(): void
    {
        $branches = Branch::factory()->count(2)->create();

        $response = $this->signedIn()
            ->get(route('stores.create'))
            ->assertOk()
            ->assertViewIs('stores.create');

        $this->assertEqualsCanonicalizing(
            $branches->pluck('id')->all(),
            $response->viewData('branches')->pluck('id')->all()
        );
    }

    /* =====================================================================
     | store()
     * =================================================================== */

    public function test_store_creates_a_store_with_all_fields(): void
    {
        $branch  = Branch::factory()->create();
        $before  = Store::count();
        $payload = $this->validPayload($branch, [
            'phone'   => '+254700000000',
            'email'   => 'store@example.com',
            'address' => '1 Market Street',
        ]);

        $this->signedIn()
            ->post(route('stores.store'), $payload)
            ->assertRedirect(route('stores.index'))
            ->assertSessionHas('success', 'Store created successfully.')
            ->assertSessionHasNoErrors();

        $this->assertSame($before + 1, Store::count());
        $this->assertDatabaseHas('stores', [
            'name'      => 'Central Store',
            'code'      => 'STR-001',
            'branch_id' => $branch->id,
            'is_active' => true,
            'location'  => 'Downtown',
            'phone'     => '+254700000000',
            'email'     => 'store@example.com',
            'address'   => '1 Market Street',
        ]);
    }

    public function test_store_creates_a_store_with_only_required_fields(): void
    {
        $branch = Branch::factory()->create();

        $this->signedIn()
            ->post(route('stores.store'), $this->validPayload($branch))
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', [
            'code' => 'STR-001', 'phone' => null, 'email' => null, 'address' => null,
        ]);
    }

    public function test_store_persists_inactive_stores(): void
    {
        $branch = Branch::factory()->create();

        $this->signedIn()
            ->post(route('stores.store'), $this->validPayload($branch, ['is_active' => 0]))
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', ['code' => 'STR-001', 'is_active' => false]);
    }

    public function test_store_treats_blank_optional_fields_as_null(): void
    {
        $branch = Branch::factory()->create();

        $this->signedIn()
            ->post(route('stores.store'), $this->validPayload($branch, ['phone' => '', 'email' => '', 'address' => '']))
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', ['code' => 'STR-001', 'phone' => null, 'email' => null, 'address' => null]);
    }

    #[DataProvider('requiredFields')]
    public function test_store_requires_field_when_missing(string $field): void
    {
        $branch  = Branch::factory()->create();
        $before  = Store::count();
        $payload = $this->validPayload($branch);
        unset($payload[$field]);

        $this->signedIn()
            ->from(route('stores.create'))
            ->post(route('stores.store'), $payload)
            ->assertRedirect(route('stores.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Store::count());
    }

    #[DataProvider('requiredFields')]
    public function test_store_requires_field_when_blank(string $field): void
    {
        $branch = Branch::factory()->create();
        $before = Store::count();

        $this->signedIn()
            ->from(route('stores.create'))
            ->post(route('stores.store'), $this->validPayload($branch, [$field => '']))
            ->assertRedirect(route('stores.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Store::count());
    }

    #[DataProvider('invalidFieldValues')]
    public function test_store_rejects_invalid_values(string $field, mixed $value): void
    {
        $branch = Branch::factory()->create();
        $before = Store::count();

        $this->signedIn()
            ->from(route('stores.create'))
            ->post(route('stores.store'), $this->validPayload($branch, [$field => $value]))
            ->assertRedirect(route('stores.create'))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, Store::count());
    }

    public function test_store_rejects_a_branch_that_does_not_exist(): void
    {
        $branch = Branch::factory()->create();
        $before = Store::count();

        $this->signedIn()
            ->from(route('stores.create'))
            ->post(route('stores.store'), $this->validPayload($branch, ['branch_id' => $this->missingBranchId()]))
            ->assertRedirect(route('stores.create'))
            ->assertSessionHasErrors('branch_id');

        $this->assertSame($before, Store::count());
    }

    #[DataProvider('maxLengthBoundaries')]
    public function test_store_accepts_values_exactly_at_the_maximum_length(string $field, int $length): void
    {
        $branch = Branch::factory()->create();
        $value  = str_repeat('a', $length);

        $this->signedIn()
            ->post(route('stores.store'), $this->validPayload($branch, [$field => $value]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', [$field => $value]); // also proves the column is wide enough
    }

    public function test_store_rejects_a_duplicate_code(): void
    {
        $branch = Branch::factory()->create();
        $this->makeStore($branch, ['code' => 'STR-001']);
        $before = Store::count();

        $this->signedIn()
            ->from(route('stores.create'))
            ->post(route('stores.store'), $this->validPayload($branch, ['name' => 'Duplicate']))
            ->assertRedirect(route('stores.create'))
            ->assertSessionHasErrors('code');

        $this->assertSame($before, Store::count());
        $this->assertDatabaseMissing('stores', ['name' => 'Duplicate']);
    }

    /* =====================================================================
     | show()
     * =================================================================== */

    public function test_show_displays_the_store_with_relations_loaded(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $this->stock($store, Product::factory()->create(), 3, 5);

        $shown = $this->signedIn()
            ->get(route('stores.show', $store))
            ->assertOk()
            ->assertViewIs('stores.show')
            ->assertViewHas('store')
            ->viewData('store');

        $this->assertTrue($shown->is($store));
        $this->assertTrue($shown->relationLoaded('branch'));
        $this->assertTrue($shown->branch->is($branch));
        $this->assertTrue($shown->relationLoaded('inventories'));
        $this->assertTrue($shown->inventories->first()->relationLoaded('product'));
    }

    public function test_show_returns_zero_metrics_for_an_empty_store(): void
    {
        $store = $this->makeStore();

        $shown = $this->signedIn()->get(route('stores.show', $store))->assertOk()->viewData('store');

        $this->assertNumber(0, $shown->users_count, 'users_count');
        $this->assertNumber(0, $shown->products_count, 'products_count');
        $this->assertNumber(0, $shown->total_units, 'total_units');
        $this->assertNumber(0, $shown->inventory_value, 'inventory_value');
        $this->assertNumber(0, $shown->sales_today, 'sales_today');
        $this->assertNumber(0, $shown->sales_this_month, 'sales_this_month');
    }

    public function test_show_counts_only_this_stores_users(): void
    {
        $store = $this->makeStore();
        $other = $this->makeStore();
        $store->users()->attach(User::factory()->count(3)->create());
        $other->users()->attach(User::factory()->count(2)->create());

        $shown = $this->signedIn()->get(route('stores.show', $store))->assertOk()->viewData('store');

        $this->assertNumber(3, $shown->users_count, 'users_count');
    }

    /** Documents CURRENT behaviour: a zero-quantity inventory row still counts as a product. */
    public function test_show_counts_zero_quantity_inventory_rows_as_products(): void
    {
        $store = $this->makeStore();
        $this->stock($store, Product::factory()->create(), 0, 9.99);

        $shown = $this->signedIn()->get(route('stores.show', $store))->assertOk()->viewData('store');

        $this->assertNumber(1, $shown->products_count, 'products_count');
        $this->assertNumber(0, $shown->total_units, 'total_units');
        $this->assertNumber(0, $shown->inventory_value, 'inventory_value');
    }

    public function test_show_sales_today_includes_day_boundaries_and_excludes_other_days_and_stores(): void
    {
        $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $other  = $this->makeStore($branch);

        $this->recordSale($store, 999.00, '2026-03-14 23:59:59');  // yesterday
        $this->recordSale($store, 10.00, '2026-03-15 00:00:00');   // first second of today
        $this->recordSale($store, 20.50, '2026-03-15 23:59:59');   // last second of today
        $this->recordSale($store, 999.00, '2026-03-16 00:00:00');  // tomorrow
        $this->recordSale($other, 5000.00, '2026-03-15 10:00:00'); // other store

        $shown = $this->signedIn()->get(route('stores.show', $store))->assertOk()->viewData('store');

        $this->assertNumber(30.50, $shown->sales_today, 'sales_today');
    }

    public function test_show_sales_this_month_includes_month_boundaries_and_excludes_other_periods(): void
    {
        $this->travelTo(Carbon::parse('2026-03-15 12:00:00'));

        $store = $this->makeStore();
        $other = $this->makeStore();

        $this->recordSale($store, 999.00, '2026-02-28 23:59:59');  // previous month
        $this->recordSale($store, 100.00, '2026-03-01 00:00:00');  // first second of month
        $this->recordSale($store, 50.00, '2026-03-15 12:00:00');   // today
        $this->recordSale($store, 25.25, '2026-03-31 23:59:59');   // last second of month
        $this->recordSale($store, 999.00, '2026-04-01 00:00:00');  // next month
        $this->recordSale($store, 999.00, '2025-03-15 12:00:00');  // same month, previous year
        $this->recordSale($other, 5000.00, '2026-03-10 09:00:00'); // other store

        $shown = $this->signedIn()->get(route('stores.show', $store))->assertOk()->viewData('store');

        $this->assertNumber(175.25, $shown->sales_this_month, 'sales_this_month');
        $this->assertNumber(50.00, $shown->sales_today, 'sales_today');
    }

    public function test_show_returns_404_for_a_missing_store(): void
    {
        $this->signedIn()->get(route('stores.show', $this->missingStoreId()))->assertNotFound();
    }

    /* =====================================================================
     | edit()
     * =================================================================== */

    public function test_edit_displays_the_form_with_the_store_and_all_branches(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $spare  = Branch::factory()->create();

        $response = $this->signedIn()
            ->get(route('stores.edit', $store))
            ->assertOk()
            ->assertViewIs('stores.edit')
            ->assertViewHas('store', fn($shown) => $shown->is($store));

        $this->assertEqualsCanonicalizing(
            [$branch->id, $spare->id],
            $response->viewData('branches')->pluck('id')->all()
        );
    }

    public function test_edit_returns_404_for_a_missing_store(): void
    {
        $this->signedIn()->get(route('stores.edit', $this->missingStoreId()))->assertNotFound();
    }

    /* =====================================================================
     | update()
     * =================================================================== */

    public function test_update_changes_all_fields_including_the_branch(): void
    {
        $oldBranch = Branch::factory()->create();
        $newBranch = Branch::factory()->create();
        $store     = $this->makeStore($oldBranch, [
            'name'  => 'Old', 'code'         => 'OLD-1', 'location'          => 'Old Loc', 'is_active' => true,
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old Address',
        ]);
        $other = $this->makeStore($oldBranch, ['name' => 'Untouched']);

        $payload = [
            'name'     => 'New', 'code'      => 'NEW-1', 'branch_id'  => $newBranch->id, 'is_active'  => 0,
            'location' => 'New Loc', 'phone' => '0711111111', 'email' => 'new@example.com', 'address' => 'New Address',
        ];

        $this->signedIn()
            ->put(route('stores.update', $store), $payload)
            ->assertRedirect(route('stores.index'))
            ->assertSessionHas('success', 'Store updated successfully.')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stores', array_merge($payload, ['id' => $store->id, 'is_active' => false]));
        $this->assertDatabaseMissing('stores', ['code' => 'OLD-1']);
        $this->assertDatabaseHas('stores', ['id' => $other->id, 'name' => 'Untouched', 'branch_id' => $oldBranch->id]);
    }

    public function test_update_allows_keeping_the_stores_own_code(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch, ['code' => 'KEEP-1']);

        $this->signedIn()
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'KEEP-1', 'name' => 'Renamed']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'code' => 'KEEP-1', 'name' => 'Renamed']);
    }

    public function test_update_rejects_a_code_used_by_another_store(): void
    {
        $branch = Branch::factory()->create();
        $this->makeStore($branch, ['code' => 'TAKEN-1']);
        $store = $this->makeStore($branch, ['code' => 'MINE-1', 'name' => 'Original']);

        $this->signedIn()
            ->from(route('stores.edit', $store))
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'TAKEN-1', 'name' => 'Changed']))
            ->assertRedirect(route('stores.edit', $store))
            ->assertSessionHasErrors('code');

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'code' => 'MINE-1', 'name' => 'Original']);
    }

    public function test_update_rejects_a_branch_that_does_not_exist(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);

        $this->signedIn()
            ->from(route('stores.edit', $store))
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'UPD-1', 'branch_id' => $this->missingBranchId()]))
            ->assertRedirect(route('stores.edit', $store))
            ->assertSessionHasErrors('branch_id');

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'branch_id' => $branch->id]);
    }

    public function test_update_clears_optional_fields_when_sent_blank(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch, ['phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old']);

        $this->signedIn()
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'UPD-1', 'phone' => '', 'email' => '', 'address' => '']))
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'phone' => null, 'email' => null, 'address' => null]);
    }

    public function test_update_keeps_optional_fields_that_are_not_submitted(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch, ['phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old']);

        $this->signedIn()
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'UPD-1', 'name' => 'Only Name']))
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', [
            'id'    => $store->id, 'name'    => 'Only Name',
            'phone' => '0700000000', 'email' => 'old@example.com', 'address' => 'Old',
        ]);
    }

    public function test_update_does_not_touch_the_stores_users_inventory_or_sales(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $user   = User::factory()->create();
        $store->users()->attach($user);
        $this->stock($store, Product::factory()->create(), 7, 2);
        $this->recordSale($store, 50.00, '2026-01-01 10:00:00');

        $this->signedIn()
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'CHG-1']))
            ->assertRedirect(route('stores.index'));

        $this->assertEqualsCanonicalizing([$user->id], $this->assignedUserIds($store));
        $this->assertDatabaseHas('inventories', ['store_id' => $store->id, 'quantity' => 7]);
        $this->assertDatabaseHas('sales', ['store_id' => $store->id]);
    }

    #[DataProvider('requiredFields')]
    public function test_update_requires_field_when_missing(string $field): void
    {
        $branch  = Branch::factory()->create();
        $store   = $this->makeStore($branch, ['name' => 'Original']);
        $payload = $this->validPayload($branch, ['code' => 'UPD-1']);
        unset($payload[$field]);

        $this->signedIn()
            ->from(route('stores.edit', $store))
            ->put(route('stores.update', $store), $payload)
            ->assertRedirect(route('stores.edit', $store))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => 'Original']);
    }

    #[DataProvider('invalidFieldValues')]
    public function test_update_rejects_invalid_values(string $field, mixed $value): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch, ['name' => 'Original']);

        $this->signedIn()
            ->from(route('stores.edit', $store))
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'UPD-1', $field => $value]))
            ->assertRedirect(route('stores.edit', $store))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => 'Original']);
    }

    #[DataProvider('maxLengthBoundaries')]
    public function test_update_accepts_values_exactly_at_the_maximum_length(string $field, int $length): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $value  = str_repeat('a', $length);

        $this->signedIn()
            ->put(route('stores.update', $store), $this->validPayload($branch, ['code' => 'UPD-1', $field => $value]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('stores.index'));

        $this->assertDatabaseHas('stores', ['id' => $store->id, $field => $value]);
    }

    public function test_update_returns_404_for_a_missing_store(): void
    {
        $branch = Branch::factory()->create();

        $this->signedIn()
            ->put(route('stores.update', $this->missingStoreId()), $this->validPayload($branch))
            ->assertNotFound();

        $this->assertDatabaseMissing('stores', ['code' => 'STR-001']);
    }

    /* =====================================================================
     | destroy()
     * =================================================================== */

    public function test_destroy_deletes_the_store_and_leaves_others_and_the_branch_intact(): void
    {
        $branch = Branch::factory()->create();
        $store  = $this->makeStore($branch);
        $other  = $this->makeStore($branch);

        $this->signedIn()
            ->delete(route('stores.destroy', $store))
            ->assertRedirect(route('stores.index'))
            ->assertSessionHas('success', 'Store deleted successfully.');

        $this->assertDatabaseMissing('stores', ['id' => $store->id]); // A7: assertSoftDeleted if SoftDeletes
        $this->assertModelExists($other);
        $this->assertModelExists($branch);
    }

    /**
     * The controller has NO guard on destroy(). What happens to assigned users
     * therefore depends entirely on the migration (cascade vs restrict). If this
     * fails with an integrity-constraint error, that is a real finding.
     */
    public function test_destroy_keeps_user_accounts_of_a_store_that_had_users(): void
    {
        $store = $this->makeStore();
        $user  = User::factory()->create();
        $store->users()->attach($user);

        $this->signedIn()
            ->delete(route('stores.destroy', $store))
            ->assertRedirect(route('stores.index'))
            ->assertSessionHas('success', 'Store deleted successfully.');

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
        $this->assertModelExists($user);
    }

    public function test_destroy_returns_404_for_a_missing_store(): void
    {
        $this->signedIn()->delete(route('stores.destroy', $this->missingStoreId()))->assertNotFound();
    }

    /* =====================================================================
     | editUser()
     * =================================================================== */

    public function test_edit_users_lists_only_role_3_users(): void
    {
        $store  = $this->makeStore();
        $staffA = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $staffB = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $other  = User::factory()->create(['role_id' => self::ROLE_OTHER]);

        $response = $this->signedIn()
            ->get(route(self::ROUTE_EDIT_USERS, $store))
            ->assertOk()
            ->assertViewIs('stores.edit-users')
            ->assertViewHas('store', fn($shown) => $shown->is($store));

        $users = $response->viewData('users');

        $this->assertTrue($users->contains('id', $staffA->id));
        $this->assertTrue($users->contains('id', $staffB->id));
        $this->assertFalse($users->contains('id', $other->id));
        $this->assertTrue($users->every(fn($u) => (int) $u->role_id === self::ROLE_STORE_STAFF));
    }

    public function test_edit_users_returns_404_for_a_missing_store(): void
    {
        $this->signedIn()->get(route(self::ROUTE_EDIT_USERS, $this->missingStoreId()))->assertNotFound();
    }

    /* =====================================================================
     | updateUser()
     * =================================================================== */

    public function test_update_users_assigns_the_selected_users(): void
    {
        $store   = $this->makeStore();
        [$a, $b] = User::factory()->count(2)->create(['role_id' => self::ROLE_STORE_STAFF])->all();

        $this->submitUsers($store, ['users' => [$a->id, $b->id]])
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHas('success', 'Store users updated successfully.')
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $this->assignedUserIds($store));
    }

    public function test_update_users_removes_unselected_and_adds_new_ones(): void
    {
        $store               = $this->makeStore();
        [$keep, $drop, $add] = User::factory()->count(3)->create(['role_id' => self::ROLE_STORE_STAFF])->all();
        $store->users()->attach([$keep->id, $drop->id]);

        $this->submitUsers($store, ['users' => [$keep->id, $add->id]])
            ->assertRedirect(route('stores.show', $store));

        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $this->assignedUserIds($store));
        $this->assertModelExists($drop);
    }

    public function test_update_users_does_not_duplicate_an_already_assigned_user(): void
    {
        $store = $this->makeStore();
        $user  = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($user);

        $this->submitUsers($store, ['users' => [$user->id]])->assertRedirect(route('stores.show', $store));

        $this->assertSame(1, $store->users()->count());
    }

    public function test_update_users_with_an_empty_array_detaches_everyone(): void
    {
        $store = $this->makeStore();
        $users = User::factory()->count(2)->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($users);

        $this->submitUsers($store, ['users' => []])
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHas('success', 'Store users updated successfully.');

        $this->assertSame([], $this->assignedUserIds($store));
        $users->each(fn($u) => $this->assertModelExists($u));
    }

    public function test_update_users_with_users_omitted_detaches_everyone(): void
    {
        $store = $this->makeStore();
        $store->users()->attach(User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]));

        $this->submitUsers($store, [])
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHasNoErrors();

        $this->assertSame([], $this->assignedUserIds($store));
    }

    public function test_update_users_does_not_change_other_stores_assignments(): void
    {
        $store  = $this->makeStore();
        $other  = $this->makeStore();
        $shared = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $newOne = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $other->users()->attach($shared);

        $this->submitUsers($store, ['users' => [$newOne->id]]);

        $this->assertEqualsCanonicalizing([$shared->id], $this->assignedUserIds($other));
        $this->assertEqualsCanonicalizing([$newOne->id], $this->assignedUserIds($store));
    }

    #[DataProvider('invalidUsersPayloads')]
    public function test_update_users_rejects_malformed_input(mixed $users, string $errorKey): void
    {
        $store    = $this->makeStore();
        $existing = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $store);

        $this->submitUsers($store, ['users' => $users], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors($errorKey);

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($store));
    }

    public function test_update_users_rejects_duplicate_user_ids(): void
    {
        $store    = $this->makeStore();
        $existing = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $user     = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $store);

        $this->submitUsers($store, ['users' => [$user->id, $user->id]], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors('users.0');

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($store));
    }

    public function test_update_users_rejects_a_user_id_that_does_not_exist(): void
    {
        $store    = $this->makeStore();
        $existing = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($existing);
        $from = route(self::ROUTE_EDIT_USERS, $store);

        $this->submitUsers($store, ['users' => [$existing->id, $this->missingUserId()]], $from)
            ->assertRedirect($from)
            ->assertSessionHasErrors('users.1');

        $this->assertEqualsCanonicalizing([$existing->id], $this->assignedUserIds($store));
    }

    /**
     * Documents CURRENT behaviour: validation only checks that the user exists,
     * not that they are role 3, so a crafted request can assign anyone.
     */
    public function test_update_users_currently_allows_assigning_a_user_who_is_not_role_3(): void
    {
        $store = $this->makeStore();
        $other = User::factory()->create(['role_id' => self::ROLE_OTHER]);

        $this->submitUsers($store, ['users' => [$other->id]])
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$other->id], $this->assignedUserIds($store));
    }

    public function test_update_users_returns_404_for_a_missing_store(): void
    {
        $user = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);

        $this->signedIn()
            ->call(self::METHOD_UPDATE_USERS, route(self::ROUTE_UPDATE_USERS, $this->missingStoreId()), ['users' => [$user->id]])
            ->assertNotFound();
    }

    /* =====================================================================
     | removeUser()
     * =================================================================== */

    public function test_remove_user_detaches_only_that_user_from_this_store(): void
    {
        $store             = $this->makeStore();
        $other             = $this->makeStore();
        [$removed, $stays] = User::factory()->count(2)->create(['role_id' => self::ROLE_STORE_STAFF])->all();

        $store->users()->attach([$removed->id, $stays->id]);
        $other->users()->attach($removed);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$store, $removed]))
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHas('success', 'User removed from store successfully.');

        $this->assertEqualsCanonicalizing([$stays->id], $this->assignedUserIds($store));
        $this->assertEqualsCanonicalizing([$removed->id], $this->assignedUserIds($other));
        $this->assertModelExists($removed);
    }

    public function test_remove_user_who_is_not_in_the_store_still_succeeds_without_side_effects(): void
    {
        $store    = $this->makeStore();
        $member   = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $stranger = User::factory()->create(['role_id' => self::ROLE_STORE_STAFF]);
        $store->users()->attach($member);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$store, $stranger]))
            ->assertRedirect(route('stores.show', $store))
            ->assertSessionHas('success', 'User removed from store successfully.');

        $this->assertEqualsCanonicalizing([$member->id], $this->assignedUserIds($store));
    }

    public function test_remove_user_returns_404_for_a_missing_store(): void
    {
        $user = User::factory()->create();

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$this->missingStoreId(), $user->id]))
            ->assertNotFound();
    }

    public function test_remove_user_returns_404_for_a_missing_user(): void
    {
        $store  = $this->makeStore();
        $member = User::factory()->create();
        $store->users()->attach($member);

        $this->signedIn()
            ->call(self::METHOD_REMOVE_USER, route(self::ROUTE_REMOVE_USER, [$store, $this->missingUserId()]))
            ->assertNotFound();

        $this->assertEqualsCanonicalizing([$member->id], $this->assignedUserIds($store));
    }
}
