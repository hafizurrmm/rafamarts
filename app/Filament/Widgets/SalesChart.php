<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'সেলস ও প্রফিট গ্রাফ (গত ৭ দিন)';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));

        $sales = $days->map(function ($day) {
            return Order::whereDate('created_at', $day)->sum('total_amount');
        });

        return [
            'datasets' => [
                [
                    'label' => 'মোট বিক্রি (৳)',
                    'data' => $sales->toArray(),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
