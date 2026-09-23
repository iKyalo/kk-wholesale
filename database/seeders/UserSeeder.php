<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kkwholesale.co.ke'],
            [
                'name'     => 'Dr System Administrator',
                'password' => Hash::make('password'),
                'role_id'  => 1,
            ]
        );

        User::updateOrCreate(
            ['email' => 'branchmanager@kkwholesale.co.ke'],
            [
                'name'     => 'Mr Branch Manager',
                'password' => Hash::make('password'),
                'role_id'  => 2,
            ]
        );

        User::updateOrCreate(
            ['email' => 'storemanager@kkwholesale.co.ke'],
            [
                'name'     => 'Mrs Store Manager',
                'password' => Hash::make('password'),
                'role_id'  => 3,
            ]
        );
    }
}
