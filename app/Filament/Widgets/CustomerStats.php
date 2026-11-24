<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\Widget;

class CustomerStats extends Widget
{
    protected static bool $isDiscovered = false;

    protected static string $view = 'filament.widgets.customer-stats';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $grouped = fn () => Order::query()->whereNotNull('customer_phone')->select('customer_phone')->groupBy('customer_phone');

        $total = $grouped()->get()->count();
        $new = $grouped()->havingRaw('MIN(created_at) >= ?', [now()->startOfMonth()])->get()->count();
        $repeat = $grouped()->havingRaw('COUNT(*) > 1')->get()->count();
        $accounts = User::where('role', 'customer')->count();

        return ['cards' => [
            ['label' => 'মোট কাস্টমার', 'count' => $total, 'icon' => 'heroicon-m-users', 'color' => '#059669', 'tint' => '#d9f4e7'],
            ['label' => 'এই মাসে নতুন', 'count' => $new, 'icon' => 'heroicon-m-user-plus', 'color' => '#2563eb', 'tint' => '#e0ecff'],
            ['label' => 'রিপিট কাস্টমার', 'count' => $repeat, 'icon' => 'heroicon-m-arrow-path', 'color' => '#7c3aed', 'tint' => '#ede4ff'],
            ['label' => 'রেজিস্টার্ড একাউন্ট', 'count' => $accounts, 'icon' => 'heroicon-m-identification', 'color' => '#d97706', 'tint' => '#fef3c7'],
        ]];
    }
}
