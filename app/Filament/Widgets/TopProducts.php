<?php

namespace App\Filament\Widgets;

use App\Support\SalesStats;
use Filament\Widgets\Widget;

class TopProducts extends Widget
{
    protected static ?int $sort = -4;

    protected static string $view = 'filament.widgets.top-products';

    protected int | string | array $columnSpan = ['default' => 'full', 'lg' => 2];

    protected function getViewData(): array
    {
        $rows = SalesStats::topProducts(5);
        $max = max(1, (int) collect($rows)->max('units'));

        return ['rows' => $rows, 'max' => $max];
    }
}
