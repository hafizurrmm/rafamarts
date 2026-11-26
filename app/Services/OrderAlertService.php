<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * নতুন অর্ডারে কাস্টমারকে SMS / কনফার্মেশন কল পাঠায় — যেকোনো প্রোভাইডারের HTTP API দিয়ে।
 * সুইচ অন + API URL (ও Key) দেওয়া থাকলে তবেই চলে; না থাকলে কিছুই করে না।
 * ব্যর্থ হলে শুধু লগ — অর্ডার কখনো আটকায় না।
 */
class OrderAlertService
{
    public static function notifyNewOrder(Order $order): void
    {
        self::send('sms', $order);
        self::send('call', $order);
    }

    /** সুইচ অন আছে এবং API URL দেওয়া আছে কিনা */
    public static function ready(string $kind): bool
    {
        $on = $kind === 'sms' ? 'sms_enabled' : 'ai_call_enabled';

        return (bool) Setting::get($on, false) && trim((string) Setting::get("{$kind}_api_url")) !== '';
    }

    public static function normalizePhone(string $phone): string
    {
        $d = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($d, '880')) {
            return $d;
        }
        if (str_starts_with($d, '0')) {
            return '88' . $d;
        }

        return '880' . $d;
    }

    private static function send(string $kind, Order $order): void
    {
        try {
            if (! self::ready($kind)) {
                return;
            }

            $get = fn ($k, $d = '') => trim((string) Setting::get($k, $d));
            $message = strtr($get('sms_message', 'আপনার অর্ডার {order_number} গ্রহণ করা হয়েছে। মোট ৳{total}। ধন্যবাদ।'), self::vars($kind, $order, ''));
            $vars = self::vars($kind, $order, $message);

            $url = $get("{$kind}_api_url");
            $method = strtoupper($get("{$kind}_method", 'GET')) === 'POST' ? 'POST' : 'GET';
            $body = $get("{$kind}_body");

            // URL এর ভেতরে মানগুলো URL-encode হয়ে বসে
            $url = strtr($url, array_map('rawurlencode', $vars));

            $req = Http::timeout(15);

            if ($method === 'GET') {
                $res = $req->get($url);
            } elseif ($body !== '' && str_starts_with($body, '{')) {
                // JSON বডি — মানগুলো JSON-safe করে বসানো হয়
                $safe = array_map(fn ($v) => trim(json_encode((string) $v, JSON_UNESCAPED_UNICODE), '"'), $vars);
                $res = $req->withBody(strtr($body, $safe), 'application/json')->post($url);
            } else {
                parse_str(strtr($body, array_map('rawurlencode', $vars)), $form);
                $res = $req->asForm()->post($url, $form);
            }

            if (! $res->successful()) {
                Log::warning("OrderAlert {$kind} failed", ['status' => $res->status(), 'body' => mb_substr($res->body(), 0, 300)]);
            }
        } catch (\Throwable $e) {
            Log::warning("OrderAlert {$kind} error: " . $e->getMessage());
        }
    }

    private static function vars(string $kind, Order $order, string $message): array
    {
        return [
            '{phone}' => self::normalizePhone((string) $order->customer_phone),
            '{name}' => (string) $order->customer_name,
            '{order_number}' => (string) $order->order_number,
            '{total}' => number_format((float) $order->total_amount, 0, '.', ''),
            '{message}' => $message,
            '{api_key}' => trim((string) Setting::get("{$kind}_api_key")),
            '{sender_id}' => trim((string) Setting::get($kind === 'sms' ? 'sms_sender_id' : 'call_caller_id')),
        ];
    }
}
