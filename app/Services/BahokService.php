<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Bahok is a smaller Bangladeshi courier aggregator. Unlike Steadfast/Pathao,
 * it doesn't have widely-documented public API docs at the time of writing,
 * so this wraps the common "API token in header, JSON in/out" pattern used
 * by most BD courier aggregators. Confirm the exact endpoint paths and field
 * names with Bahok's own API documentation/merchant panel before going live —
 * you may need to adjust `createOrder()`'s endpoint/payload keys accordingly.
 */
class BahokService
{
    protected string $baseUrl = 'https://api.bahok.com.bd/api/v1'; // confirm against Bahok's docs

    protected function apiToken(): ?string
    {
        return Setting::get('bahok_api_token');
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('bahok_enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled() && filled($this->apiToken());
    }

    protected function client()
    {
        return Http::withToken($this->apiToken())
            ->acceptJson()
            ->baseUrl($this->baseUrl);
    }

    public function createOrder(Order $order): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Bahok API টোকেন সেট করা নেই অথবা Bahok বন্ধ (OFF) আছে। Settings এ গিয়ে চালু করুন।'];
        }

        $response = $this->client()->post('/orders', [
            'invoice' => $order->order_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total_amount : 0,
            'note' => $order->customer_note ?? '',
        ]);

        $data = $response->json();

        $order->update([
            'courier_name' => 'bahok',
            'courier_order_id' => $data['data']['id'] ?? $data['id'] ?? null,
            'tracking_code' => $data['data']['tracking_code'] ?? $data['tracking_code'] ?? null,
            'courier_status' => 'sent',
            'courier_response' => json_encode($data),
        ]);

        return [
            'success' => $response->successful(),
            'message' => $response->successful() ? 'Bahok এ অর্ডার পাঠানো হয়েছে।' : 'Bahok এ অর্ডার পাঠাতে ব্যর্থ হয়েছে। এন্ডপয়েন্ট/ফিল্ড ঠিক আছে কিনা Bahok এর ডকুমেন্টেশনে যাচাই করুন।',
            'data' => $data,
        ];
    }

    public function checkStatus(Order $order): array
    {
        if (!$order->courier_order_id) {
            return ['success' => false, 'message' => 'এই অর্ডারের কোনো Bahok কনসাইনমেন্ট আইডি নেই।'];
        }

        $response = $this->client()->get("/orders/{$order->courier_order_id}");
        $data = $response->json();

        if ($response->successful() && isset($data['data']['status'])) {
            $order->update(['courier_status' => $data['data']['status']]);
        }

        return [
            'success' => $response->successful(),
            'status' => $data['data']['status'] ?? null,
            'data' => $data,
        ];
    }
}
