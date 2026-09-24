<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => fake()->unique()->company() . ' Branch',
            'location' => fake()->city(),
            'phone'    => fake()->unique()->numerify('07########'),
            'email'    => fake()->unique()->safeEmail(),
        ];
    }
}
