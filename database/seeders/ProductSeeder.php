<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku'           => 'KK-0001',
                'name'          => 'Premium Maize Flour 2kg',
                'category'      => 'Food & Groceries',
                'cost_price'    => 140,
                'selling_price' => 180,
                'minimum_stock' => 10,
                'is_active'     => 1,
            ],
            [
                'sku'           => 'KK-0002',
                'name'          => 'Wheat Flour 2kg',
                'category'      => 'Food & Groceries',
                'cost_price'    => 130,
                'selling_price' => 170,
                'minimum_stock' => 10,
                'is_active'     => 1,
            ],
            [
                'sku'           => 'KK-0003',
                'name'          => 'White Sugar 1kg',
                'category'      => 'Food & Groceries',
                'cost_price'    => 150,
                'selling_price' => 190,
                'minimum_stock' => 10,
                'is_active'     => 1,
            ],
            [
                'sku'           => 'KK-0004',
                'name'          => 'Cooking Oil 1L',
                'category'      => 'Food & Groceries',
                'cost_price'    => 220,
                'selling_price' => 280,
                'minimum_stock' => 10,
                'is_active'     => 1,
            ],
            [
                'sku'           => 'KK-0005',
                'name'          => 'Rice 5kg',
                'category'      => 'Food & Groceries',
                'cost_price'    => 650,
                'selling_price' => 800,
                'minimum_stock' => 10,
                'is_active'     => 1,
            ],
        ];

        foreach ($products as $index => $product) {
            $sku = 'KK-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);

            DB::table('products')->updateOrInsert(
                ['sku' => $sku],
                [
                    'name'          => $product['name'],
                    'category'      => $product['category'],
                    'cost_price'    => $product['cost_price'],
                    'selling_price' => $product['selling_price'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }
    }
}
