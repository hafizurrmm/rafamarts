<?php

namespace App\Services;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * নতুন অর্ডারের খবর Telegram এ পাঠায়।
     * কোনো কারণে ব্যর্থ হলে শুধু লগ করে — কাস্টমারের অর্ডার কখনো আটকায় না।
     */
    public static function notifyNewOrder(Order $order): void
    {
        try {
            if (! Setting::get('telegram_enabled', false)) {
                return;
            }

            $token  = trim((string) Setting::get('telegram_bot_token'));
            $chatId = trim((string) Setting::get('telegram_chat_id'));

            if ($token === '' || $chatId === '') {
                return;
            }

            $order->refresh()->loadMissing('items');

            $items = $order->items
                ->map(fn ($i) => '• ' . e($i->product_name) . ' × ' . $i->quantity)
                ->implode("\n");

            $payment = $order->isMobilePayment()
                ? '💳 ' . e($order->paymentLabel()) . ' — TrxID: <code>' . e($order->payment_trx_id) . '</code>'
                    . "\n📲 প্রেরক: " . e($order->payment_sender_number) . ' (⏳ যাচাই বাকি)'
                : '💵 ক্যাশ অন ডেলিভারি';

            $text = "🛒 <b>নতুন অর্ডার!</b>\n"
                . '<b>' . e($order->order_number) . "</b>\n\n"
                . '👤 ' . e($order->customer_name) . "\n"
                . '📞 ' . e($order->customer_phone) . "\n"
                . '📍 ' . e($order->customer_address) . "\n\n"
                . $items . "\n\n"
                . '💰 মোট: ৳' . number_format((float) $order->total_amount, 0) . "\n"
                . $payment;

            $payload = [
                'chat_id'                  => $chatId,
                'text'                     => $text,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true,
            ];

            // "অর্ডার দেখুন" বাটন — ট্যাপ করলে সরাসরি অ্যাডমিনে অর্ডার খুলবে।
            // (Telegram শুধু https লিংক বাটনে নেয়, লোকাল http হলে বাটন বাদ যাবে)
            $url = OrderResource::getUrl('edit', ['record' => $order]);
            if (str_starts_with($url, 'https://')) {
                $payload['reply_markup'] = json_encode([
                    'inline_keyboard' => [[['text' => '📦 অর্ডার দেখুন', 'url' => $url]]],
                ]);
            }

            $response = Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", $payload);

            if (! $response->successful()) {
                Log::warning('Telegram notify failed: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram notify failed: ' . $e->getMessage());
        }
    }

    /** Settings পেজ থেকে "টেস্ট মেসেজ পাঠান" বাটনের জন্য। [ok, message] ফেরত দেয়। */
    public static function sendTest(?string $token, ?string $chatId): array
    {
        $token  = trim((string) $token);
        $chatId = trim((string) $chatId);

        if ($token === '' || $chatId === '') {
            return [false, 'Bot Token ও Chat ID দুটোই দিন।'];
        }

        try {
            $response = Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => '✅ টেস্ট সফল! নতুন অর্ডার এলে এভাবেই নোটিফিকেশন আসবে।',
            ]);

            if ($response->successful()) {
                return [true, 'টেস্ট মেসেজ পাঠানো হয়েছে, Telegram চেক করুন।'];
            }

            return [false, $response->json('description') ?? 'Telegram এরর দিয়েছে।'];
        } catch (\Throwable $e) {
            return [false, 'Telegram এ পৌঁছানো যায়নি: ' . $e->getMessage()];
        }
    }

    /** টোকেন ঠিক কিনা দেখে বটের username ফেরত দেয়। [username|null, errorMessage|null] */
    public static function botUsername(?string $token): array
    {
        $token = trim((string) $token);
        if ($token === '') {
            return [null, 'আগে Bot Token বসান।'];
        }

        try {
            $res = Http::timeout(8)->get("https://api.telegram.org/bot{$token}/getMe");
            if ($res->successful() && $res->json('result.username')) {
                return [$res->json('result.username'), null];
            }

            return [null, 'Bot Token ঠিক নয়: ' . ($res->json('description') ?? 'অজানা এরর')];
        } catch (\Throwable $e) {
            return [null, 'Telegram এ পৌঁছানো যায়নি: ' . $e->getMessage()];
        }
    }

    /**
     * "/start কোড" মেসেজ পাঠানো চ্যাট খুঁজে তার Chat ID ফেরত দেয়।
     * [chatId|null, errorMessage|null, chatTitle|null]
     */
    public static function detectChat(?string $token, string $code): array
    {
        $token = trim((string) $token);
        if ($token === '' || $code === '') {
            return [null, 'আগে "১. বট খুলুন" বাটন চাপুন।', null];
        }

        try {
            // ওয়েবহুক সেট থাকলে getUpdates কাজ করে না — তাই আগে সরিয়ে নিই
            Http::timeout(8)->post("https://api.telegram.org/bot{$token}/deleteWebhook");

            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/getUpdates", [
                'limit' => 100,
                'allowed_updates' => ['message'],
            ]);

            if (! $res->successful()) {
                return [null, $res->json('description') ?? 'Telegram এরর দিয়েছে।', null];
            }

            $found = null;
            foreach (array_reverse((array) $res->json('result', [])) as $u) {
                $text = trim((string) data_get($u, 'message.text'));
                if ($text === "/start {$code}") {
                    $found = data_get($u, 'message.chat');
                    break;
                }
            }

            if (! $found) {
                return [null, 'এখনো Telegram এ Start চাপা হয়নি। বট খুলে Start চেপে আবার চেষ্টা করুন।', null];
            }

            $title = trim((string) (data_get($found, 'title') ?: data_get($found, 'first_name') . ' ' . data_get($found, 'last_name')));

            return [(string) data_get($found, 'id'), null, $title];
        } catch (\Throwable $e) {
            return [null, 'Telegram এ পৌঁছানো যায়নি: ' . $e->getMessage(), null];
        }
    }
}
