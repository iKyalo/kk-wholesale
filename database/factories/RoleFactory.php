<?php
namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
            ],
            [
                'name' => 'Branch Manager',
                'slug' => 'branch_manager',
            ],
            [
                'name' => 'Store Manager',
                'slug' => 'store_manager',
            ],
        ];

        return fake()->randomElement($roles);
    }
}
