<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

/**
 * কুরিয়ার স্ট্যাটাস অটো-সিঙ্ক: চলমান অর্ডারগুলোর কুরিয়ার স্ট্যাটাস কুরিয়ারের API থেকে এনে আপডেট করে।
 * চালু থাকলে Delivered → অর্ডার Completed, Returned → অর্ডার Returned (শুধু Pending/Processing/On Hold থেকে)।
 */
class CourierSync
{
    private const OPEN_ORDER_STATUSES = ['pending', 'processing', 'on_hold'];

    public static function enabled(): bool
    {
        return (bool) Setting::get('courier_auto_sync', true);
    }

    public static function updatesOrderStatus(): bool
    {
        return (bool) Setting::get('courier_sync_update_order', true);
    }

    /** কুরিয়ারের কাঁচা স্ট্যাটাস লেখা থেকে delivered / returned চেনা; আংশিক ডেলিভারি বা অজানা হলে null */
    public static function finalKind(?string $raw): ?string
    {
        $s = strtolower(trim((string) $raw));
        if ($s === '' || str_contains($s, 'partial')) {
            return null;
        }
        if (str_contains($s, 'return')) {
            return 'returned';
        }
        if (str_contains($s, 'deliver') && ! str_contains($s, 'pending') && ! str_contains($s, 'undeliver') && ! str_contains($s, 'failed')) {
            return 'delivered';
        }

        return null;
    }

    private function serviceFor(string $courier): object
    {
        return match ($courier) {
            'pathao' => app(PathaoService::class),
            'redx' => app(RedXService::class),
            'bahok' => app(BahokService::class),
            default => app(SteadfastService::class),
        };
    }

    /** @return array{checked:int, changed:int, completed:int, returned:int, errors:int, skipped:int} */
    public function run(int $limit = 50, int $days = 45): array
    {
        $stats = ['checked' => 0, 'changed' => 0, 'completed' => 0, 'returned' => 0, 'errors' => 0, 'skipped' => 0];

        $candidates = Order::query()
            ->whereNotNull('courier_name')
            ->where(fn ($q) => $q->whereNotNull('consignment_id')->orWhereNotNull('courier_order_id'))
            ->whereIn('status', self::OPEN_ORDER_STATUSES)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByRaw('courier_synced_at IS NOT NULL, courier_synced_at ASC')
            ->limit($limit * 3)
            ->get()
            // কুরিয়ার আগেই ফাইনাল বলে দিয়েছে (কিন্তু অটো-আপডেট বন্ধ) — বারবার চেক করার দরকার নেই
            ->reject(fn (Order $o) => self::finalKind($o->courier_status) !== null)
            ->take($limit);

        $updateOrder = self::updatesOrderStatus();

        foreach ($candidates as $order) {
            if (! Setting::get($order->courier_name . '_enabled', false)) {
                $stats['skipped']++;
                continue;
            }

            try {
                $before = $order->courier_status;
                $result = $this->serviceFor($order->courier_name)->checkStatus($order);
                $stats['checked']++;

                $order->refresh();
                if (($result['success'] ?? false) && $order->courier_status !== $before) {
                    $stats['changed']++;
                }

                if ($updateOrder && in_array($order->status, self::OPEN_ORDER_STATUSES, true)) {
                    $kind = self::finalKind($order->courier_status);
                    if ($kind === 'delivered') {
                        $order->update(['status' => 'completed']);
                        $stats['completed']++;
                    } elseif ($kind === 'returned') {
                        $order->update(['status' => 'returned']);
                        $stats['returned']++;
                    }
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                report($e);
            } finally {
                // হুক/ইভেন্ট এড়াতে সরাসরি কোয়েরি
                Order::whereKey($order->getKey())->update(['courier_synced_at' => now()]);
            }

            usleep(250000); // কুরিয়ারের API রেট-লিমিট এড়াতে
        }

        Setting::set('courier_last_sync', now()->format('d M Y, h:i A') . ' — চেক ' . $stats['checked']
            . ', বদলেছে ' . $stats['changed'] . ', Completed ' . $stats['completed'] . ', Returned ' . $stats['returned']
            . ($stats['errors'] ? ', ত্রুটি ' . $stats['errors'] : ''));
        Setting::set('courier_last_sync_at', now()->timestamp);

        return $stats;
    }
}
