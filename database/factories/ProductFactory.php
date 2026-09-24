<?php
namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 50, 2000);

        return [
            'sku'           => 'KK-' . fake()->unique()->numerify('####'),
            'name'          => fake()->unique()->words(3, true),
            'category'      => fake()->randomElement([
                'Food & Groceries',
                'Beverages',
                'Household',
                'Personal Care',
                'Stationery',
            ]),
            'cost_price'    => $costPrice,
            'selling_price' => $costPrice + fake()->randomFloat(2, 10, 500),
            'minimum_stock' => fake()->numberBetween(5, 50),
            'is_active'     => true,
        ];
    }
}
