<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Café Orgánico Premium',
                'code' => 'CAF-001',
                'description' => 'Café orgánico de altura, tueste medio',
                'purchase_price' => 80.00,
                'sale_price' => 120.00,
                'category' => 'organic',
                'origin' => 'Chiapas',
                'roast_level' => 'medium',
                'initial_stock' => 100,
                'minimum_stock' => 20,
            ],
            [
                'name' => 'Café Arábica Gourmet',
                'code' => 'CAF-002',
                'description' => 'Café arábica de primera calidad',
                'purchase_price' => 90.00,
                'sale_price' => 150.00,
                'category' => 'gourmet',
                'origin' => 'Veracruz',
                'roast_level' => 'medium-dark',
                'initial_stock' => 75,
                'minimum_stock' => 15,
            ],
            [
                'name' => 'Café Descafeinado',
                'code' => 'CAF-003',
                'description' => 'Café descafeinado suave',
                'purchase_price' => 70.00,
                'sale_price' => 110.00,
                'category' => 'decaf',
                'origin' => 'Oaxaca',
                'roast_level' => 'medium',
                'initial_stock' => 50,
                'minimum_stock' => 10,
            ],
        ];

        foreach ($products as $productData) {
            $initialStock = $productData['initial_stock'];
            $minimumStock = $productData['minimum_stock'];
            unset($productData['initial_stock'], $productData['minimum_stock']);

            $product = Product::create($productData);

            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $initialStock,
                'minimum_stock' => $minimumStock,
            ]);
        }
    }
}