<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Staff
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@staff.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        // Products
        \App\Models\Product::create([
            'name' => 'Laptop Pro',
            'sku' => 'LAP-001',
            'price' => 1200.00,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        \App\Models\Product::create([
            'name' => 'Wireless Mouse',
            'sku' => 'MOU-002',
            'price' => 25.50,
            'stock_quantity' => 100,
            'status' => 'active',
        ]);

        \App\Models\Product::create([
            'name' => 'Keyboard Mechanical',
            'sku' => 'KEY-003',
            'price' => 75.00,
            'stock_quantity' => 50,
            'status' => 'active',
        ]);

        // Generate 100 random products
        \App\Models\Product::factory(100)->create();
    }
}
