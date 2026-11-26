<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -6;

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $totalOrders = Order::count();

        $pendingOrders = Order::where('status', 'pending')->count();

        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // Orders that still need to be pushed to a courier
        $courierSyncNeeded = Order::whereIn('status', ['pending', 'processing'])
            ->whereNull('courier_name')
            ->count();

        $stockoutCount = Product::where('is_active', true)->where('stock', '<=', 0)->count();

        return [
            Stat::make('সব অর্ডার', $totalOrders)
                ->description('মোট অর্ডার সংখ্যা')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->url(\App\Filament\Resources\OrderResource::getUrl())
                ->color('primary'),

            Stat::make('পেন্ডিং অর্ডার', $pendingOrders)
                ->description('এখনো কনফার্ম হয়নি')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('বাতিল অর্ডার', $cancelledOrders)
                ->description('Cancelled orders')
                ->descriptionIcon('heroicon-m-x-circle')
                ->url(\App\Filament\Resources\OrderResource::getUrl() . '?tableFilters[status][value]=cancelled')
                ->color('danger'),

            Stat::make('কুরিয়ার সিঙ্ক', $courierSyncNeeded)
                ->description($courierSyncNeeded > 0 ? 'অ্যাকশন নিন' : 'সব সিঙ্ক করা আছে')
                ->descriptionIcon('heroicon-m-truck')
                ->color($courierSyncNeeded > 0 ? 'danger' : 'success'),

            Stat::make('স্টকআউট অ্যালার্ট', $stockoutCount)
                ->description('প্রোডাক্ট স্টক শেষ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stockoutCount > 0 ? 'danger' : 'success'),
        ];
    }
}
