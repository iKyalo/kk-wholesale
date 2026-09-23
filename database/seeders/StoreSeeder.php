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
            ],

            // Branch 2
            [
                'branch'   => 'Branch 2',
                'name'     => 'Mombasa Main Store',
                'location' => 'Mombasa CBD',
            ],
            [
                'branch'   => 'Branch 3',
                'name'     => 'Mombasa Warehouse',
                'location' => 'Changamwe',
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
