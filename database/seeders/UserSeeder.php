<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name'     => 'Test User',
            'email'    => 'test@test.com',
            'password' => Hash::make('password'),
        ]);
    }
}