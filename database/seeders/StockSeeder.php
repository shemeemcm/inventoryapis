<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Widget A — total qty: 5 (triggers +30% price)
        Stock::create([
            'product_id'   => 1,
            'warehouse_id' => 1,
            'quantity'     => 3,
            'expires_at'   => null,
        ]);
        Stock::create([
            'product_id'   => 1,
            'warehouse_id' => 2,
            'quantity'     => 2,
            'expires_at'   => null,
        ]);

        // Widget B — total qty: 30 (triggers +10% price)
        Stock::create([
            'product_id'   => 2,
            'warehouse_id' => 1,
            'quantity'     => 15,
            'expires_at'   => null,
        ]);
        Stock::create([
            'product_id'   => 2,
            'warehouse_id' => 2,
            'quantity'     => 15,
            'expires_at'   => null,
        ]);

        // Widget C — total qty: 120 (triggers -20% price)
        Stock::create([
            'product_id'   => 3,
            'warehouse_id' => 1,
            'quantity'     => 60,
            'expires_at'   => null,
        ]);
        Stock::create([
            'product_id'   => 3,
            'warehouse_id' => 3,
            'quantity'     => 60,
            'expires_at'   => null,
        ]);

        // Widget D — expiring soon (triggers -25% on expiring units)
        Stock::create([
            'product_id'   => 4,
            'warehouse_id' => 1,
            'quantity'     => 20,
            'expires_at'   => now()->addDays(3), // expires in 3 days
        ]);
        Stock::create([
            'product_id'   => 4,
            'warehouse_id' => 2,
            'quantity'     => 10,
            'expires_at'   => now()->addDays(10), // not expiring soon
        ]);
    }
}