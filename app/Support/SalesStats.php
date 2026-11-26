<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;

/** ড্যাশবোর্ডের বিক্রির হিসাব — বাতিল ও রিটার্ন বাদে */
class SalesStats
{
    private static function base()
    {
        return Order::query()->whereNotIn('status', ['cancelled', 'returned']);
    }

    public static function between(Carbon $from, Carbon $to): array
    {
        $q = self::base()->whereBetween('created_at', [$from, $to]);

        return ['revenue' => (float) (clone $q)->sum('total_amount'), 'orders' => (int) (clone $q)->count()];
    }

    public static function today(): array
    {
        return self::between(now()->startOfDay(), now()->endOfDay());
    }

    public static function month(): array
    {
        return self::between(now()->startOfMonth(), now()->endOfMonth());
    }

    /** গত মাসের একই সময় (প্রথম N দিন) পর্যন্ত — ন্যায্য তুলনার জন্য */
    public static function lastMonthSamePeriod(): array
    {
        $start = now()->subMonthNoOverflow()->startOfMonth();
        $end = $start->copy()->addDays(now()->day - 1)->endOfDay();

        return self::between($start, $end->min($start->copy()->endOfMonth()));
    }

    /** % পরিবর্তন; আগের মান ০ হলে null */
    public static function pct(float $now, float $prev): ?float
    {
        return $prev > 0 ? round((($now - $prev) / $prev) * 100, 1) : null;
    }

    public static function taka(float $n): string
    {
        if ($n >= 10000000) {
            return '৳' . round($n / 10000000, 2) . ' কোটি';
        }
        if ($n >= 100000) {
            return '৳' . round($n / 100000, 2) . ' লাখ';
        }

        return '৳' . number_format($n, 0);
    }

    public static function days(int $n): array
    {
        $out = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $q = self::base()->whereDate('created_at', $d->toDateString());
            $out[] = ['label' => $d->format('d M'), 'revenue' => (float) (clone $q)->sum('total_amount'), 'orders' => (int) (clone $q)->count()];
        }

        return $out;
    }
}
