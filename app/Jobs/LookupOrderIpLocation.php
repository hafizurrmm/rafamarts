<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

/**
 * অর্ডারের IP থেকে লোকেশন/ISP বের করে অর্ডারে সেভ করে।
 * ipwho.is (ফ্রি, কী লাগে না) ব্যবহার করা হয়েছে। কাস্টমারের রেসপন্স শেষ হওয়ার পরে চলে
 * (dispatchAfterResponse), তাই চেকআউট ধীর হয় না; ব্যর্থ হলে অর্ডারে কোনো প্রভাব পড়ে না।
 */
class LookupOrderIpLocation
{
    use Dispatchable;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (! $order || blank($order->customer_ip)) {
            return;
        }

        $ip = $order->customer_ip;

        // লোকাল/প্রাইভেট IP (নিজের কম্পিউটারে টেস্ট করার সময়) — বাইরে থেকে লোকেশন পাওয়া যায় না
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            $order->forceFill(['ip_location' => 'লোকাল/টেস্ট IP'])->saveQuietly();
            return;
        }

        try {
            $res = Http::timeout(5)
                ->get("https://ipwho.is/{$ip}", ['fields' => 'success,city,region,country,country_code,connection'])
                ->json();

            if (! ($res['success'] ?? false)) {
                return;
            }

            $location = collect([$res['city'] ?? null, $res['region'] ?? null, $res['country'] ?? null])
                ->filter()->unique()->implode(', ');

            $order->forceFill([
                'ip_location' => $location ?: null,
                'ip_country_code' => $res['country_code'] ?? null,
                'ip_isp' => $res['connection']['isp'] ?? ($res['connection']['org'] ?? null),
            ])->saveQuietly();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
