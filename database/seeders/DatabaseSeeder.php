<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters — users and warehouses before stock
        $this->call([
            UserSeeder::class,
            WarehouseSeeder::class,
            ProductSeeder::class,
            StockSeeder::class,
        ]);
    }
}