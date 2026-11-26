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
        $hour = (int) now()->format('G');

        return [
            'greeting' => $hour < 12 ? 'শুভ সকাল' : ($hour < 17 ? 'শুভ দুপুর' : ($hour < 20 ? 'শুভ সন্ধ্যা' : 'শুভ রাত্রি')),
            'name' => auth()->user()?->name,
            'shop' => \App\Models\Setting::get('site_name') ?: config('shop.name'),
            'date' => now()->translatedFormat('l, d F Y'),
            'todayOrders' => $today['orders'],
            'todaySales' => SalesStats::taka($today['revenue']),
            'pending' => Order::where('status', 'pending')->count(),
            'pct' => SalesStats::pct($month['revenue'], $prev['revenue']),
            'newOrdersUrl' => \App\Filament\Resources\OrderResource::getUrl('create'),
            'ordersUrl' => \App\Filament\Resources\OrderResource::getUrl('index'),
        ];
    }
}
