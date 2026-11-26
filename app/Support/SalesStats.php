<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    private static function items(Carbon $from, Carbon $to)
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereBetween('o.created_at', [$from, $to]);
    }

    /**
     * লাভের হিসাব: মোট লাভ = Σ (বিক্রয়মূল্য − ক্রয়মূল্য) × পরিমাণ; নেট লাভ = মোট লাভ − খরচ।
     * ক্রয়মূল্য আইটেমে সেভ থাকলে সেটা, না থাকলে প্রোডাক্টের বর্তমান ক্রয়মূল্য।
     * @return array{gross: float, expenses: float, net: float, missing: int, items: int}
     */
    public static function profit(Carbon $from, Carbon $to): array
    {
        $row = self::items($from, $to)->selectRaw(
            'COALESCE(SUM((oi.price - COALESCE(oi.cost_price, p.cost_price, 0)) * oi.quantity),0) as gross,
             COUNT(*) as items,
             SUM(CASE WHEN COALESCE(oi.cost_price, p.cost_price) IS NULL THEN 1 ELSE 0 END) as missing'
        )->first();

        $exp = (float) \App\Models\Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])->sum('amount');
        $gross = (float) ($row->gross ?? 0);

        return ['gross' => $gross, 'expenses' => $exp, 'net' => $gross - $exp, 'missing' => (int) ($row->missing ?? 0), 'items' => (int) ($row->items ?? 0)];
    }

    /** সেরা N প্রোডাক্ট (বিক্রি হওয়া ইউনিট অনুযায়ী) */
    public static function topProducts(int $n = 5): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->groupBy('oi.product_name')
            ->selectRaw('oi.product_name as name, SUM(oi.quantity) as units, SUM(oi.line_total) as revenue')
            ->orderByDesc('units')->limit($n)->get()->all();
    }

    /** ক্যাটাগরি অনুযায়ী আয় */
    public static function byCategory(int $n = 6): array
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('products as p', 'p.id', '=', 'oi.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->groupBy('c.name')
            ->selectRaw('c.name as name, SUM(oi.line_total) as revenue')
            ->orderByDesc('revenue')->limit($n)->get()->all();
    }
}
