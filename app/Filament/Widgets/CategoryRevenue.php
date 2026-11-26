<?php

namespace App\Filament\Widgets;

use App\Support\SalesStats;
use Filament\Widgets\Widget;

class CategoryRevenue extends Widget
{
    protected static ?int $sort = -3;

    protected static string $view = 'filament.widgets.category-revenue';

    protected int | string | array $columnSpan = 1;

    protected function getViewData(): array
    {
        $rows = SalesStats::byCategory(6);
        $total = max(1.0, (float) collect($rows)->sum('revenue'));

        return ['rows' => $rows, 'total' => $total];
    }
}
