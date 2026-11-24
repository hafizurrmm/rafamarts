<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PathaoService
{
    protected function baseUrl(): string
    {
        // Toggle to production URL once you have live Pathao credentials
        $env = Setting::get('pathao_environment', 'sandbox'); // sandbox | live

        return $env === 'live'
            ? 'https://api-hermes.pathao.com'
            : 'https://courier-api-sandbox.pathao.com';
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('pathao_enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled()
            && filled(Setting::get('pathao_client_id'))
            && filled(Setting::get('pathao_client_secret'))
            && filled(Setting::get('pathao_username'))
            && filled(Setting::get('pathao_password'))
            && filled(Setting::get('pathao_store_id'));
    }

    /**
     * Get (and cache) an OAuth access token for Pathao.
     */
    protected function accessToken(): ?string
    {
        return Cache::remember('pathao_access_token', now()->addMinutes(50), function () {
            $response = Http::baseUrl($this->baseUrl())->post('/aut/issue_token', [
                'client_id' => Setting::get('pathao_client_id'),
                'client_secret' => Setting::get('pathao_client_secret'),
                'username' => Setting::get('pathao_username'),
                'password' => Setting::get('pathao_password'),
                'grant_type' => 'password',
            ]);

            return $response->json('access_token');
        });
    }

    protected function client()
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->baseUrl($this->baseUrl());
    }

    /**
     * Create a delivery order on Pathao.
     * Requires recipient_city / recipient_zone / recipient_area IDs.
     * Use getCities()/getZones()/getAreas() to look these up and store defaults in Settings,
     * or pass them explicitly via $overrides.
     */
    public function createOrder(Order $order, array $overrides = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Pathao API credentials সেট করা হয়নি। Settings > কুরিয়ার API তে গিয়ে যোগ করুন।'];
        }

        $payload = array_merge([
            'store_id' => (int) Setting::get('pathao_store_id'),
            'merchant_order_id' => $order->order_number,
            'recipient_name' => $order->customer_name,
            'recipient_phone' => $order->customer_phone,
            'recipient_address' => $order->customer_address,
            'recipient_city' => (int) Setting::get('pathao_default_city_id'),
            'recipient_zone' => (int) Setting::get('pathao_default_zone_id'),
            'recipient_area' => (int) Setting::get('pathao_default_area_id'),
            'delivery_type' => 48, // 48 = normal delivery
            'item_type' => 2, // 2 = parcel
            'special_instruction' => $order->customer_note ?? '',
            'item_quantity' => $order->items()->sum('quantity') ?: 1,
            'item_weight' => 0.5,
            'item_description' => 'Order ' . $order->order_number,
            'amount_to_collect' => $order->payment_method === 'cod' ? (float) $order->total_amount : 0,
        ], $overrides);

        $response = $this->client()->post('/aut/api/v1/orders', $payload);
        $data = $response->json();

        $consignmentId = $data['data']['consignment_id'] ?? null;

        $order->update([
            'courier_name' => 'pathao',
            'courier_order_id' => $consignmentId,
            'tracking_code' => $consignmentId,
            'courier_status' => $data['data']['order_status'] ?? 'sent',
            'courier_response' => json_encode($data),
        ]);

        return [
            'success' => $response->successful(),
            'message' => $data['message'] ?? ($response->successful() ? 'Pathao তে অর্ডার পাঠানো হয়েছে।' : 'Pathao তে অর্ডার পাঠাতে ব্যর্থ হয়েছে।'),
            'data' => $data,
        ];
    }

    public function checkStatus(Order $order): array
    {
        if (!$order->courier_order_id) {
            return ['success' => false, 'message' => 'এই অর্ডারের কোনো Pathao consignment ID নেই।'];
        }

        $response = $this->client()->get("/aut/api/v1/orders/{$order->courier_order_id}/info");
        $data = $response->json();

        if ($response->successful() && isset($data['data']['order_status'])) {
            $order->update(['courier_status' => $data['data']['order_status']]);
        }

        return [
            'success' => $response->successful(),
            'status' => $data['data']['order_status'] ?? null,
            'data' => $data,
        ];
    }

    // --- Location lookup helpers, useful when filling in default city/zone/area in Settings ---

    public function getCities(): array
    {
        return $this->client()->get('/aut/api/v1/city-list')->json('data.data', []);
    }

    public function getZones(int $cityId): array
    {
        return $this->client()->get("/aut/api/v1/cities/{$cityId}/zone-list")->json('data.data', []);
    }

    public function getAreas(int $zoneId): array
    {
        return $this->client()->get("/aut/api/v1/zones/{$zoneId}/area-list")->json('data.data', []);
    }

    public function getStores(): array
    {
        return $this->client()->get('/aut/api/v1/stores')->json('data.data', []);
    }
}
