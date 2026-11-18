<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class UserReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'User Report';

    protected static ?string $navigationGroup = 'Report';

    protected static ?int $navigationSort = 30;

    protected static string $view = 'filament.pages.user-report';

    public $rows = [];

    public function mount(): void
    {
        $this->rows = Order::query()
            ->selectRaw('customer_name, customer_phone, COUNT(*) as total_orders, SUM(total_amount) as total_spent')
            ->groupBy('customer_name', 'customer_phone')
            ->orderByDesc('total_orders')
            ->limit(50)
            ->get();
    }
}
