<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            // Branch 1
            [
                'branch'   => 'Branch 1',
                'name'     => 'Store 1',
                'location' => 'Industrial Area',
                'phone'    => '0711000001',
                'email'    => 'store1@kkwholesale.co.ke',
            ],

            // Branch 2
            [
                'branch'   => 'Branch 2',
                'name'     => 'Store 2',
                'location' => 'Mombasa CBD',
                'phone'    => '0711000002',
                'email'    => 'store2@kkwholesale.co.ke',
            ],
            [
                'branch'   => 'Branch 2',
                'name'     => 'Store 3',
                'location' => 'Changamwe',
                'phone'    => '0711000003',
                'email'    => 'store3@kkwholesale.co.ke',
            ],
        ];

        foreach ($stores as $store) {
            $branch = DB::table('branches')
                ->where('name', $store['branch'])
                ->first();

            if (! $branch) {
                continue;
            }

            DB::table('stores')->updateOrInsert(
                [
                    'branch_id' => $branch->id,
                    'name'      => $store['name'],
                ],
                [
                    'location'   => $store['location'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
