<?php
namespace Database\Factories;

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name'      => fake()->unique()->company() . ' Store',
            'location'  => fake()->city(),
            'phone'     => fake()->unique()->numerify('07########'),
            'email'     => fake()->unique()->safeEmail(),
        ];
    }
}
