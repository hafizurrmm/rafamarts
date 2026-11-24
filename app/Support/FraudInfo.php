<?php

namespace App\Support;

use App\Models\Blacklist;
use App\Models\Order;
use App\Services\SteadfastService;
use Illuminate\Support\Facades\Cache;

/** একটা ফোন নম্বরের ডেলিভারি/রিটার্ন হিস্ট্রি — অর্ডার লিস্টের "Courier Rate" ও Fraud Checker এর জন্য */
class FraudInfo
{
    private static array $memo = [];

    /** নিজের দোকানের হিস্ট্রি (সস্তা — প্রতি অনুরোধে একবারই) */
    public static function local(?string $phone): array
    {
        $phone = trim((string) $phone);

        return self::$memo[$phone] ??= (function () use ($phone) {
            $rows = Order::where('customer_phone', $phone)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
            $total = (int) $rows->sum();
            $completed = (int) ($rows['completed'] ?? 0);
            $cancelled = (int) (($rows['cancelled'] ?? 0) + ($rows['returned'] ?? 0));
            $finished = $completed + $cancelled;

            return [
                'total' => $total,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'pending' => $total - $finished,
                'success_rate' => $finished > 0 ? (int) round($completed / $finished * 100) : null,
            ];
        })();
    }

    public static function blacklist(?string $phone): ?Blacklist
    {
        return Blacklist::where('phone_number', trim((string) $phone))->first();
    }

    /** Steadfast নেটওয়ার্ক ফ্রড স্কোর — ১০ মিনিট ক্যাশ; [data|null, error|null] */
    public static function steadfast(?string $phone): array
    {
        $service = app(SteadfastService::class);

        if (! $service->isConfigured()) {
            return [null, 'Steadfast চালু/কনফিগার করা নেই — শুধু নিজের স্টোরের হিস্ট্রি দেখানো হচ্ছে। Settings > কুরিয়ার API তে Steadfast চালু করলে নেটওয়ার্ক-ওয়াইড ফ্রড স্কোরও দেখা যাবে।'];
        }

        $phone = trim((string) $phone);
        $cached = Cache::get('fraud:sf:' . $phone);

        if (is_array($cached)) {
            return $cached;
        }

        $r = $service->checkFraud($phone);
        $out = $r['success'] ? [$r['data'], null] : [null, $r['message']];

        if ($r['success']) {
            Cache::put('fraud:sf:' . $phone, $out, now()->addMinutes(10)); // শুধু সফল উত্তর ক্যাশ হয়
        }

        return $out;
    }
}
