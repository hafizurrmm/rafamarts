<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends events to Meta's Conversions API (server-side) to complement the browser
 * Pixel. Two deliveries of the SAME event (browser pixel + this server call), tagged
 * with the SAME event_id, let Facebook deduplicate them and build a single, more
 * complete event — this is what Meta calls "Event Match Quality", and it is the
 * single biggest lever for lower CPA/CPM: more verified signal (hashed phone, IP,
 * user agent, fbp/fbc click-id cookies) per event means Meta's ad auction can find
 * better-matching audiences and optimize delivery more precisely.
 *
 * Reference: https://developers.facebook.com/docs/marketing-api/conversions-api
 */
class FacebookConversionsService
{
    protected const GRAPH_VERSION = 'v21.0';

    public function isEnabled(): bool
    {
        return (bool) Setting::get('fb_enabled', false)
            && filled(Setting::get('fb_pixel_id'))
            && filled(Setting::get('fb_access_token'));
    }

    public function pixelId(): ?string
    {
        return Setting::get('fb_pixel_id');
    }

    /**
     * Build the user_data block from the current request — call this INSIDE the
     * controller (where $request is available) before dispatching the background
     * job, since a Request object shouldn't be serialized into a queued job.
     */
    public function buildUserDataFromRequest(Request $request, array $extra = []): array
    {
        return array_filter(array_merge([
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp'),
            'fbc' => $request->cookie('_fbc'),
        ], $extra), fn ($value) => filled($value));
    }

    /**
     * Send one event to the Conversions API. $userData/$customData follow Meta's
     * schema (https://developers.facebook.com/docs/marketing-api/conversions-api/parameters).
     * PII fields in $userData (ph, em) must already be SHA-256 hashed — use self::hash().
     */
    public function sendEvent(
        string $eventName,
        string $eventId,
        array $customData,
        array $userData,
        string $eventSourceUrl
    ): void {
        if (!$this->isEnabled()) {
            return;
        }

        $pixelId = $this->pixelId();
        $accessToken = Setting::get('fb_access_token');
        $testEventCode = Setting::get('fb_test_event_code');

        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => now()->timestamp,
                'event_id' => $eventId,
                'event_source_url' => $eventSourceUrl,
                'action_source' => 'website',
                'user_data' => $userData,
                'custom_data' => $customData,
            ]],
            'access_token' => $accessToken,
        ];

        if (filled($testEventCode)) {
            $payload['test_event_code'] = $testEventCode;
        }

        try {
            $response = Http::timeout(5)
                ->post("https://graph.facebook.com/" . self::GRAPH_VERSION . "/{$pixelId}/events", $payload);

            if ($response->failed()) {
                Log::warning('Facebook CAPI event failed', [
                    'event' => $eventName,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            // Never let a Facebook API hiccup break checkout/browsing — just log it.
            Log::warning('Facebook CAPI request threw: ' . $e->getMessage());
        }
    }

    /** Meta requires PII (email, phone) to be lowercased/trimmed and SHA-256 hashed before sending. */
    public static function hash(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return hash('sha256', strtolower(trim($value)));
    }

    /**
     * Normalizes a Bangladeshi phone number to E.164-without-plus digits (8801XXXXXXXXX)
     * before hashing, since Meta matches phone numbers most reliably in that format.
     */
    public static function normalizeBdPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '88' . $digits;
        }

        if (!str_starts_with($digits, '88')) {
            return '88' . $digits;
        }

        return $digits;
    }
}
