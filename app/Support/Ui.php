<?php

namespace App\Support;

use App\Models\Setting;

/**
 * সাইটের কোন কোন অংশ দেখাবে/লুকাবে (Store Settings → সাধারণ সেটিংস)।
 * Ui::on('wishlist') | 'cart' | 'track' | 'categories' | 'flash_sale' — ডিফল্ট: দেখাবে।
 */
class Ui
{
    public static function on(string $key): bool
    {
        return (bool) Setting::get('ui_' . $key, true);
    }

    /** মোবাইল বটম বারে কয়টা আইটেম থাকছে (হোম সবসময়) */
    public static function bottomNavCount(): int
    {
        return 1
            + (int) self::on('track')
            + (int) \App\Http\Controllers\CustomerAuthController::enabled()
            + (int) self::on('wishlist')
            + (int) self::on('cart');
    }
}
