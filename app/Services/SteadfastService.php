<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class SteadfastService
{
    // Official Steadfast Courier API base URL
    protected string $baseUrl = 'https://portal.packzy.com/api/v1';

    protected function apiKey(): ?string
    {
        return Setting::get('steadfast_api_key');
    }

    protected function secretKey(): ?string
    {
        return Setting::get('steadfast_secret_key');
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('steadfast_enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled() && filled($this->apiKey()) && filled($this->secretKey());
    }

    protected function client()
    {
        return Http::withHeaders([
            'Api-Key' => $this->apiKey(),
            'Secret-Key' => $this->secretKey(),
            'Content-Type' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Create a delivery order (consignment) on Steadfast for the given order.
     */
    public function createOrder(Order $order): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Steadfast API credentials সেট করা হয়নি। Settings > কুরিয়ার API তে গিয়ে যোগ করুন।'];
        }

        $response = $this->client()->post('/create_order', [
            'invoice' => $order->order_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'cod_amount' => $order->payment_method === 'cod' ? (float) $order->total_amount : 0,
            'note' => $order->customer_note ?? '',
        ]);

        $data = $response->json();

        $order->update([
            'courier_name' => 'steadfast',
            'consignment_id' => $data['consignment']['consignment_id'] ?? null,
            'tracking_code' => $data['consignment']['tracking_code'] ?? null,
            'courier_order_id' => $data['consignment']['consignment_id'] ?? null,
            'courier_status' => $data['consignment']['status'] ?? 'sent',
            'courier_response' => json_encode($data),
        ]);

        return [
            'success' => $response->successful() && ($data['status'] ?? null) === 200,
            'message' => $data['message'] ?? ($response->successful() ? 'Steadfast এ অর্ডার পাঠানো হয়েছে।' : 'Steadfast এ অর্ডার পাঠাতে ব্যর্থ হয়েছে।'),
            'data' => $data,
        ];
    }

    /**
     * Check current delivery status of a Steadfast consignment.
     */
    public function checkStatus(Order $order): array
    {
        if (!$order->consignment_id) {
            return ['success' => false, 'message' => 'এই অর্ডারের কোনো Steadfast consignment ID নেই।'];
        }

        $response = $this->client()->get("/status_by_cid/{$order->consignment_id}");
        $data = $response->json();

        if ($response->successful() && isset($data['delivery_status'])) {
            $order->update(['courier_status' => $data['delivery_status']]);
        }

        return [
            'success' => $response->successful(),
            'status' => $data['delivery_status'] ?? null,
            'data' => $data,
        ];
    }

    /**
     * Fraud/delivery-success check by phone number — Steadfast's network-wide signal,
     * built from EVERY merchant's delivery history for that number (not just yours).
     * This is what catches a fraud customer who has never ordered from your store
     * before, which your own order history obviously can't.
     *
     * Response fields (per Steadfast): delivery_ratio / cancellation_ratio (0-100,
     * whole percent, null if the number has no finished parcels yet), volume_band
     * (none/low/medium/high/very_high — how many parcels this number has received
     * across all merchants), total_reports, fraud_categories, reasons.
     *
     * Rate-limited per merchant by Steadfast based on your own booking volume — so
     * this is only called on-demand from the Fraud Checker page / order page, never
     * in a loop or on every page load.
     */
    public function checkFraud(string $phone): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Steadfast API credentials সেট করা হয়নি। Settings > কুরিয়ার API তে গিয়ে যোগ করুন।'];
        }

        $phone = preg_replace('/\D/', '', $phone) ?? '';

        $response = $this->client()->get("/fraud_check/score/{$phone}");
        $data = $response->json();

        if (!$response->successful() || !is_array($data)) {
            return [
                'success' => false,
                'message' => 'Steadfast থেকে Fraud Check ডেটা আনা যায়নি (API সাময়িক ডাউন থাকতে পারে, বা রেট-লিমিট ছুঁয়েছে)।',
            ];
        }

        return ['success' => true, 'data' => $data];
    }
}
