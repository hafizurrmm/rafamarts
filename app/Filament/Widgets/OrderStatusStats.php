<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Widgets\Widget;

/** অর্ডার লিস্টের উপরের স্ট্যাটাস কার্ড (এক লাইনে) — ক্লিক করলে ওই স্ট্যাটাসে ফিল্টার হয় */
class OrderStatusStats extends Widget
{
    protected static bool $isDiscovered = false; // ড্যাশবোর্ডে নয়, শুধু অর্ডার পেজে

    protected static string $view = 'filament.widgets.order-status-stats';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $active = request()->query('tableFilters')['status']['value'] ?? null;

        $defs = [
            ['pending', 'Pending', 'heroicon-m-clock', '#6b7280', '#f3f4f6'],
            ['processing', 'Processing', 'heroicon-m-cube', '#2563eb', '#e0ecff'],
            ['on_hold', 'On Hold', 'heroicon-m-pause-circle', '#d97706', '#fef3c7'],
            ['completed', 'Completed', 'heroicon-m-check-circle', '#059669', '#d9f4e7'],
            ['cancelled', 'Cancelled', 'heroicon-m-x-circle', '#dc2626', '#fde2e2'],
        ];

        $cards = array_map(fn ($d) => [
            'label' => $d[1],
            'count' => (int) ($counts[$d[0]] ?? 0),
            'icon' => $d[2],
            'color' => $d[3],
            'tint' => $d[4],
            'active' => $active === $d[0],
            'url' => OrderResource::getUrl('index', ['tableFilters' => ['status' => ['value' => $d[0]]]]),
        ], $defs);

        return ['cards' => $cards, 'allUrl' => OrderResource::getUrl('index')];
    }
}
