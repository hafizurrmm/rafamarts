<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -9;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $m = \App\Support\SalesStats::month();
        $p = \App\Support\SalesStats::lastMonthSamePeriod();
        $pct = \App\Support\SalesStats::pct($m['revenue'], $p['revenue']);

        $totalOrders = Order::count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $courierSyncNeeded = Order::whereIn('status', ['pending', 'processing'])->whereNull('courier_name')->count();
        $stockoutCount = Product::where('is_active', true)->where('stock', '<=', 0)->count();

        return [
            Stat::make('এই মাসের মোট আয়', \App\Support\SalesStats::taka($m['revenue']))
                ->description($pct === null ? 'বাতিল ও রিটার্ন বাদে' : abs($pct) . '% ' . ($pct >= 0 ? 'বেশি' : 'কম') . ' গত মাসের তুলনায়')
                ->descriptionIcon($pct === null ? 'heroicon-m-banknotes' : ($pct >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'))
                ->color($pct === null || $pct >= 0 ? 'success' : 'danger'),

            Stat::make('সব অর্ডার', $totalOrders)
                ->description('মোট অর্ডার সংখ্যা')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->url(\App\Filament\Resources\OrderResource::getUrl())
                ->color('primary'),

            Stat::make('বাতিল অর্ডার', $cancelledOrders)
                ->description('Cancelled orders')
                ->descriptionIcon('heroicon-m-x-circle')
                ->url(\App\Filament\Resources\OrderResource::getUrl() . '?tableFilters[status][value]=cancelled')
                ->color('danger'),

            Stat::make('স্টকআউট অ্যালার্ট', $stockoutCount)
                ->description('প্রোডাক্ট স্টক শেষ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stockoutCount > 0 ? 'danger' : 'success'),

            Stat::make('কুরিয়ার সিঙ্ক', $courierSyncNeeded)
                ->description($courierSyncNeeded > 0 ? 'অ্যাকশন নিন' : 'সব সিঙ্ক করা আছে')
                ->descriptionIcon('heroicon-m-truck')
                ->color($courierSyncNeeded > 0 ? 'danger' : 'success'),
        ];
    }
}
