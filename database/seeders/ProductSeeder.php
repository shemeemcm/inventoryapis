<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Low stock product — will trigger +30% pricing
        Product::create([
            'name'       => 'Widget A',
            'base_price' => 100.00,
        ]);

        // Medium stock product — will trigger +10% pricing
        Product::create([
            'name'       => 'Widget B',
            'base_price' => 200.00,
        ]);

        // High stock product — will trigger -20% pricing
        Product::create([
            'name'       => 'Widget C',
            'base_price' => 150.00,
        ]);

        // Product with expiring stock — will trigger -25% on expiring units
        Product::create([
            'name'       => 'Widget D',
            'base_price' => 80.00,
        ]);
    }
}