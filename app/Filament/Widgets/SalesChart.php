<?php

namespace App\Filament\Widgets;

use App\Support\SalesStats;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'বিক্রি — গত ১৪ দিন';

    protected static ?string $description = 'বার = বিক্রি (৳), লাইন = অর্ডার সংখ্যা (বাতিল ও রিটার্ন বাদে)';

    protected static ?int $sort = -8;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $d = collect(SalesStats::days(14));

        return [
            'datasets' => [
                [
                    'type' => 'bar',
                    'label' => 'বিক্রি (৳)',
                    'data' => $d->pluck('revenue')->all(),
                    'backgroundColor' => '#0a74b8',
                    'borderRadius' => 6,
                    'yAxisID' => 'y',
                    'order' => 2,
                ],
                [
                    'type' => 'line',
                    'label' => 'অর্ডার',
                    'data' => $d->pluck('orders')->all(),
                    'borderColor' => '#ff9f1c',
                    'backgroundColor' => '#ff9f1c',
                    'tension' => 0.35,
                    'pointRadius' => 3,
                    'yAxisID' => 'y1',
                    'order' => 1,
                ],
            ],
            'labels' => $d->pluck('label')->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'position' => 'left'],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false], 'ticks' => ['precision' => 0]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
