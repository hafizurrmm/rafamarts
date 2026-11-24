<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;

class MonthlyReport extends Widget
{
    protected static string $view = 'filament.widgets.monthly-report';

    // TodayReport (sort 2) এর ঠিক পরে (পাশে) বসবে
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $pollingInterval = '30s';

    protected function getViewData(): array
    {
        $month = Order::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        $byStatus = (clone $month)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $byStatus->sum();
        $completed = (int) ($byStatus['completed'] ?? 0);
        $cancelled = (int) (($byStatus['cancelled'] ?? 0) + ($byStatus['returned'] ?? 0));
        $finished = $completed + $cancelled;

        return [
            'monthLabel' => now()->format('F Y'),
            'total' => $total,
            'rows' => [
                'Pending'    => (int) ($byStatus['pending'] ?? 0),
                'Processing' => (int) ($byStatus['processing'] ?? 0),
                'On Hold'    => (int) ($byStatus['on_hold'] ?? 0),
                'Canceled'   => (int) ($byStatus['cancelled'] ?? 0),
                'Returned'   => (int) ($byStatus['returned'] ?? 0),
                'Completed'  => $completed,
            ],
            // বাতিল বাদে এ মাসের মোট অর্ডারের টাকা
            'amount' => (float) (clone $month)->whereNotIn('status', ['cancelled', 'returned'])->sum('total_amount'),
            // শুধু ডেলিভারি সম্পন্ন অর্ডারের টাকা
            'completedAmount' => (float) (clone $month)->where('status', 'completed')->sum('total_amount'),
            // ডেলিভারি সাকসেস রেট (শুধু শেষ হওয়া অর্ডার: completed ÷ (completed + cancelled))
            'successRate' => $finished > 0 ? round($completed / $finished * 100) : null,
        ];
    }
}
