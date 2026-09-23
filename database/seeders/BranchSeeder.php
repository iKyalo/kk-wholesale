<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name'     => 'Branch 1',
                'location' => 'Nairobi CBD',
                'phone'    => '0711000001',
                'email'    => 'branch1@kkwholesale.co.ke',
            ],
            [
                'name'     => 'Branch 2',
                'location' => 'Mombasa',
                'phone'    => '0711000002',
                'email'    => 'branch2@kkwholesale.co.ke',
            ],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['name' => $branch['name']],
                array_merge($branch, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
