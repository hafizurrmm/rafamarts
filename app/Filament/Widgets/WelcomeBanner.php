<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\SalesStats;
use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    protected static ?int $sort = -10;

    protected static string $view = 'filament.widgets.welcome-banner';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $today = SalesStats::today();
        $month = SalesStats::month();
        $prev = SalesStats::lastMonthSamePeriod();
        $dhaka = now('Asia/Dhaka');
        $bn = fn (string $v) => strtr($v, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
        $days = ['রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার'];
        $months = ['', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

        return [
            'greeting' => 'স্বাগতম',
            'name' => auth()->user()?->name,
            'shop' => \App\Models\Setting::get('site_name') ?: config('shop.name'),
            'date' => $days[(int) $dhaka->format('w')] . ', ' . $bn($dhaka->format('j')) . ' ' . $months[(int) $dhaka->format('n')] . ' ' . $bn($dhaka->format('Y')),
            'todayOrders' => $today['orders'],
            'todaySales' => SalesStats::taka($today['revenue']),
            'pending' => Order::where('status', 'pending')->count(),
            'pct' => SalesStats::pct($month['revenue'], $prev['revenue']),
            'newProductUrl' => \App\Filament\Resources\ProductResource::getUrl('create'),
            'ordersUrl' => \App\Filament\Resources\OrderResource::getUrl('index'),
        ];
    }
}
