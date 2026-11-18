<?php

namespace App\Filament\Pages;

use App\Models\OrderItem;
use Filament\Pages\Page;

class ProductReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Product';

    protected static ?string $navigationGroup = 'Report';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.product-report';

    public $rows = [];

    public function mount(): void
    {
        $this->rows = OrderItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(line_total) as total_sales')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(20)
            ->get();
    }
}
