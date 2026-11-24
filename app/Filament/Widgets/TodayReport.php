<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class TodayReport extends Widget
{
    protected static string $view = 'filament.widgets.today-report';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    // নতুন অর্ডার এলে সংখ্যা নিজে থেকেই আপডেট হবে
    protected static ?string $pollingInterval = '15s';

    protected function getViewData(): array
    {
        $today = Order::whereDate('created_at', today());

        $byStatus = (clone $today)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return [
            'total' => (int) $byStatus->sum(),
            'rows' => [
                'Pending'    => (int) ($byStatus['pending'] ?? 0),
                'Processing' => (int) ($byStatus['processing'] ?? 0),
                'On Hold'    => (int) ($byStatus['on_hold'] ?? 0),
                'Canceled'   => (int) ($byStatus['cancelled'] ?? 0),
                'Returned'   => (int) ($byStatus['returned'] ?? 0),
                'Completed'  => (int) ($byStatus['completed'] ?? 0),
            ],
            // বাতিল বাদে আজকের অর্ডারের মোট টাকা
            'amount' => (float) (clone $today)->whereNotIn('status', ['cancelled', 'returned'])->sum('total_amount'),
        ];
    }
}
