<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::create([
            'name'      => 'Main Warehouse',
            'latitude'  => 51.5074,
            'longitude' => -0.1278,
        ]);

        Warehouse::create([
            'name'      => 'North Warehouse',
            'latitude'  => 53.4808,
            'longitude' => -2.2426,
        ]);

        Warehouse::create([
            'name'      => 'South Warehouse',
            'latitude'  => 50.8225,
            'longitude' => -0.1372,
        ]);
    }
}