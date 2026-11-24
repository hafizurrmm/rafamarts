<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * RedX (ShopUp) Open API — https://openapi.redx.com.bd/v1.0.0-beta (sandbox: https://sandbox.redx.com.bd/v1.0.0-beta)
 * হেডার: API-ACCESS-TOKEN: Bearer {token}। পার্সেল তৈরি: POST /parcel, ট্র্যাক: GET /parcel/track/{id}।
 * delivery_area / delivery_area_id বাধ্যতামূলক — Settings এ ডিফল্ট এরিয়া দিতে হয় (RedX এর /areas থেকে আইডি নিন)।
 * ⚠ লাইভ একাউন্ট ছাড়া পরীক্ষা করা হয়নি — প্রথমে Sandbox এ একটা অর্ডার পাঠিয়ে মিলিয়ে নিন।
 */
class RedXService
{
    protected function baseUrl(): string
    {
        return Setting::get('redx_environment', 'sandbox') === 'live'
            ? 'https://openapi.redx.com.bd/v1.0.0-beta'
            : 'https://sandbox.redx.com.bd/v1.0.0-beta';
    }

    protected function token(): string
    {
        $t = trim((string) Setting::get('redx_access_token'));

        return str_starts_with(strtolower($t), 'bearer ') ? $t : 'Bearer ' . $t; // "Bearer " আগে থেকে থাকলে দ্বিতীয়বার বসে না
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('redx_enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled() && filled(Setting::get('redx_access_token'));
    }

    protected function client()
    {
        return Http::withHeaders(['API-ACCESS-TOKEN' => $this->token()])
            ->acceptJson()
            ->timeout(30)
            ->baseUrl($this->baseUrl());
    }

    public function createOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'RedX টোকেন সেট করা নেই অথবা RedX বন্ধ (OFF) আছে। Settings > কুরিয়ার API তে গিয়ে চালু করুন।'];
        }

        $areaId = (int) Setting::get('redx_default_area_id');
        $areaName = (string) Setting::get('redx_default_area_name');

        if (! $areaId || $areaName === '') {
            return ['success' => false, 'message' => 'RedX এর ডিফল্ট ডেলিভারি এরিয়া (নাম ও আইডি) সেট করা নেই। Settings > কুরিয়ার API > RedX এ দিন।'];
        }

        $order->loadMissing('items');
        $total = (float) $order->total_amount;
        $payload = [
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'delivery_area' => $areaName,
            'delivery_area_id' => $areaId,
            'customer_address' => $order->customer_address,
            'merchant_invoice_id' => $order->order_number,
            'cash_collection_amount' => (string) ($order->isMobilePayment() ? 0 : (int) round($total)),
            'parcel_weight' => 500, // গ্রাম
            'instruction' => (string) ($order->customer_note ?? ''),
            'value' => (int) round($total),
            'parcel_details_json' => $order->items->map(fn ($i) => [
                'name' => $i->product_name,
                'category' => 'general',
                'value' => (int) round((float) ($i->line_total ?? 0)),
            ])->values()->all(),
        ];

        if ($store = (int) Setting::get('redx_pickup_store_id')) {
            $payload['pickup_store_id'] = $store;
        }

        $response = $this->client()->post('/parcel', $payload);
        $data = $response->json() ?? [];
        $tracking = $data['tracking_id'] ?? $data['data']['tracking_id'] ?? null;

        if ($response->successful() && $tracking) {
            $order->update([
                'courier_name' => 'redx',
                'courier_order_id' => $tracking,
                'tracking_code' => $tracking,
                'courier_status' => 'sent',
                'courier_response' => json_encode($data),
            ]);
        }

        return [
            'success' => $response->successful() && (bool) $tracking,
            'message' => $response->successful() && $tracking
                ? 'RedX এ অর্ডার পাঠানো হয়েছে।'
                : 'RedX এ অর্ডার পাঠাতে ব্যর্থ: ' . ($data['message'] ?? $data['error'] ?? ('HTTP ' . $response->status())),
            'data' => $data,
        ];
    }

    public function checkStatus(Order $order): array
    {
        if (! $order->courier_order_id) {
            return ['success' => false, 'message' => 'এই অর্ডারের কোনো RedX ট্র্যাকিং আইডি নেই।'];
        }

        $response = $this->client()->get('/parcel/track/' . $order->courier_order_id);
        $data = $response->json() ?? [];

        $events = $data['tracking'] ?? $data['data'] ?? [];
        $last = is_array($events) && $events ? end($events) : null;
        $status = is_array($last) ? ($last['message_en'] ?? $last['status'] ?? null) : ($data['status'] ?? null);

        if ($response->successful() && $status) {
            $order->update(['courier_status' => (string) $status]);
        }

        return ['success' => $response->successful(), 'status' => $status, 'data' => $data];
    }
}
