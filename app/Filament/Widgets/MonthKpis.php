<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * বাতিল: এই উইজেট আর ড্যাশবোর্ডে দেখায় না।
 * (ফাইলটা রাখা হয়েছে কারণ cPanel ডেপ্লয়ে cp দিয়ে কপি হয়, পুরনো ফাইল মোছা হয় না —
 *  সার্ভারে পুরনো MonthKpis থেকে গেলে এটা সেটাকে ওভাররাইট করে লুকিয়ে দেয়।)
 */
class MonthKpis extends Widget
{
    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.widgets.top-products';

    public static function canView(): bool
    {
        return false;
    }
}
