<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** অর্ডার লিস্টের উপরের স্ট্যাটাস কার্ড — কার্ডে ক্লিক করলে ওই স্ট্যাটাসের অর্ডার ফিল্টার হয় */
class OrderStatusStats extends BaseWidget
{
    protected static bool $isDiscovered = false; // ড্যাশবোর্ডে নয়, শুধু অর্ডার পেজে

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $cards = [
            ['pending', 'Pending', 'heroicon-m-clock', 'gray'],
            ['processing', 'Processing', 'heroicon-m-cube', 'info'],
            ['on_hold', 'On Hold', 'heroicon-m-pause-circle', 'warning'],
            ['completed', 'Completed', 'heroicon-m-check-circle', 'success'],
            ['cancelled', 'Cancelled', 'heroicon-m-x-circle', 'danger'],
        ];

        return array_map(fn ($c) => Stat::make($c[1], (int) ($counts[$c[0]] ?? 0))
            ->icon($c[2])
            ->color($c[3])
            ->url(OrderResource::getUrl('index', ['tableFilters' => ['status' => ['value' => $c[0]]]])), $cards);
    }
}
