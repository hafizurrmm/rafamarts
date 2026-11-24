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
        // ⚠️ এই সিডার admin@nexmart.com / password নামে দুর্বল অ্যাডমিন বানায় — শুধু লোকাল/ডেমোর জন্য।
        // ক্লায়েন্টের লাইভ সাইটে ভুলে চালালে ঠেকাতে প্রোডাকশনে বন্ধ। ইনস্টলের জন্য `php artisan shop:install` ব্যবহার করুন।
        if (app()->isProduction()) {
            $this->command?->error('DemoSeeder প্রোডাকশনে চলবে না। php artisan shop:install ব্যবহার করুন।');

            return;
        }

        // Admin user for Filament login
        User::updateOrCreate(
            ['email' => 'admin@nexmart.com'],
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
