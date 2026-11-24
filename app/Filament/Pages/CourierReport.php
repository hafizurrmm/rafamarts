<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;

class CourierReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'কুরিয়ার রিপোর্ট';

    protected static ?string $navigationGroup = 'Report';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.courier-report';

    public $rows = [];

    public function mount(): void
    {
        $this->rows = Order::query()
            ->selectRaw('courier_name, courier_status, COUNT(*) as total')
            ->whereNotNull('courier_name')
            ->groupBy('courier_name', 'courier_status')
            ->orderBy('courier_name')
            ->get();
    }
}
