<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user for Filament login
        User::updateOrCreate(
            ['email' => 'admin@shopbd.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $categories = [
            'Headphones', 'Gadget & Electronics', 'Home And Living', 'Ice Silk Socks', 'Hair Clip',
        ];

        foreach ($categories as $i => $name) {
            $category = Category::create([
                'name' => $name,
                'sort_order' => $i,
            ]);

            for ($p = 1; $p <= 5; $p++) {
                $price = rand(200, 2000);
                $compare = rand(0, 1) ? $price + rand(100, 800) : null;

                Product::create([
                    'category_id' => $category->id,
                    'name' => $name . " Product #{$p}",
                    'price' => $price,
                    'compare_price' => $compare,
                    'stock' => rand(5, 50),
                    'is_flash_sale' => $p <= 2,
                    'is_active' => true,
                ]);
            }
        }
    }
}
