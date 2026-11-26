<?php

namespace App\Http\Controllers;

use App\Jobs\LookupOrderIpLocation;
use App\Jobs\SendFacebookConversionEvent;
use App\Models\AbandonedCheckout;
use App\Models\Blacklist;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\User;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use App\Services\FacebookConversionsService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index(Request $request, FacebookConversionsService $fb)
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'আপনার কার্ট খালি।');
        }

        // InitiateCheckout — tells Facebook's algorithm this visitor is a serious,
        // high-intent prospect (not just a browser), which helps it find more
        // people like them for retargeting and lookalike audiences.
        $fbEventId = (string) Str::uuid();

        if ($fb->isEnabled()) {
            $value = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

            SendFacebookConversionEvent::dispatch(
                'InitiateCheckout',
                $fbEventId,
                [
                    'content_ids' => collect($cart)->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
                    'content_type' => 'product',
                    'num_items' => collect($cart)->sum('quantity'),
                    'value' => (float) $value,
                    'currency' => 'BDT',
                ],
                $fb->buildUserDataFromRequest($request),
                $request->fullUrl(),
            )->afterResponse();
        }

        $paymentMethods = Order::availablePaymentMethods();

        return view('checkout.index', compact('cart', 'fbEventId', 'paymentMethods'));
    }

    /**
     * ফর্ম পূরণের মাঝপথে (ফোন নম্বর দেওয়ার পর) তথ্য সেভ করে রাখে — কাস্টমার অর্ডার না করে চলে গেলে
     * অ্যাডমিন ফোন/WhatsApp করে ফলো-আপ করতে পারবে। এখানে কিছু ভুল হলেও চেকআউটে কোনো প্রভাব পড়ে না।
     */
    public function capture(Request $request)
    {
        try {
            $cart = Session::get('cart', []);
            $phone = AbandonedCheckout::normalizePhone($request->input('customer_phone'));

            if (empty($cart) || ! $phone || Blacklist::isBlocked($phone)) {
                return response()->noContent();
            }

            $items = collect($cart)->map(fn ($i) => [
                'id' => $i['id'],
                'name' => $i['name'],
                'price' => (float) $i['price'],
                'quantity' => (int) $i['quantity'],
            ])->values()->all();

            $zone = $request->input('delivery_zone');
            $zone = array_key_exists($zone, Order::DELIVERY_CHARGES) ? $zone : null;

            AbandonedCheckout::updateOrCreate(
                ['session_id' => Session::getId()],
                [
                    'customer_name' => Str::limit(trim((string) $request->input('customer_name')), 120, ''),
                    'customer_phone' => $phone,
                    'customer_address' => Str::limit(trim((string) $request->input('customer_address')), 500, ''),
                    'delivery_zone' => $zone,
                    'cart' => $items,
                    'total_amount' => collect($items)->sum(fn ($i) => $i['price'] * $i['quantity']),
                    'customer_ip' => $request->ip(),
                    'last_seen_at' => now(),
                    'recovered_at' => null,
                    'order_id' => null,
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->noContent();
    }

    public function store(Request $request)
    {
        $paymentMethods = Order::availablePaymentMethods();

        // TrxID সবসময় বড় হাতের অক্ষরে ও স্পেস ছাড়া — তাহলে "abc123" আর "ABC123" একই ধরা হবে
        $request->merge([
            'payment_method' => $request->input('payment_method', array_key_first($paymentMethods)),
            'payment_trx_id' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('payment_trx_id'))),
        ]);

        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string',
            'customer_note' => 'nullable|string',
            'delivery_zone' => 'required|in:inside_dhaka,near_dhaka,outside_dhaka',
            'payment_method' => ['required', Rule::in(array_keys($paymentMethods))],
        ];

        $isMobilePayment = in_array($request->input('payment_method'), Order::MOBILE_PAYMENT_METHODS, true);

        if ($isMobilePayment) {
            $rules['payment_trx_id'] = ['required', 'string', 'min:6', 'max:40', 'regex:/^[A-Z0-9]+$/', Rule::unique('orders', 'payment_trx_id')];
            $rules['payment_sender_number'] = ['required', 'string', 'max:20'];
        }

        $validated = $request->validate($rules, [
            'payment_method.in' => 'এই পেমেন্ট মেথডটি এখন চালু নেই, অন্যটি বেছে নিন।',
            'payment_trx_id.required' => 'পেমেন্টের TrxID দিন।',
            'payment_trx_id.min' => 'TrxID ঠিকভাবে দিন (কমপক্ষে ৬ অক্ষর)।',
            'payment_trx_id.regex' => 'TrxID তে শুধু ইংরেজি অক্ষর ও সংখ্যা থাকবে।',
            'payment_trx_id.unique' => 'এই TrxID দিয়ে আগেই অর্ডার করা হয়েছে। সঠিক TrxID দিন।',
            'payment_sender_number.required' => 'যে নম্বর থেকে টাকা পাঠিয়েছেন সেটা দিন।',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'আপনার কার্ট খালি।');
        }

        // --- Auto-fraud protection (mirrors the Security & Fraud settings toggles) ---

        if (Blacklist::isBlocked($validated['customer_phone'])) {
            return back()
                ->withInput()
                ->with('error', 'দুঃখিত, এই নম্বর থেকে অর্ডার নেওয়া সম্ভব হচ্ছে না। বিস্তারিত জানতে আমাদের হটলাইনে যোগাযোগ করুন।');
        }

        if (Setting::get('fraud_auto_block_repeat_orders', false)) {
            $recentOrders = Order::where('customer_phone', $validated['customer_phone'])
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recentOrders >= 3) {
                // Auto-blacklist this number after too many orders within an hour
                Blacklist::updateOrCreate(
                    ['phone_number' => $validated['customer_phone']],
                    [
                        'name' => $validated['customer_name'],
                        'reason' => 'এক ঘণ্টায় ৩টির বেশি অর্ডার — অটো-ব্লক',
                        'is_active' => true,
                    ]
                );

                return back()
                    ->withInput()
                    ->with('error', 'অস্বাভাবিক সংখ্যক অর্ডারের কারণে সাময়িকভাবে অর্ডার নেওয়া বন্ধ আছে। পরে আবার চেষ্টা করুন অথবা হটলাইনে যোগাযোগ করুন।');
            }
        }

        $subTotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $deliveryCharge = Order::DELIVERY_CHARGES[$validated['delivery_zone']];
        $total = $subTotal + $deliveryCharge;

        $order = DB::transaction(function () use ($validated, $cart, $subTotal, $deliveryCharge, $total, $request, $isMobilePayment) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'customer_note' => $validated['customer_note'] ?? null,
                'customer_ip' => $request->ip(),
                'sub_total' => $subTotal,
                'delivery_charge' => $deliveryCharge,
                'total_amount' => $total,
                'delivery_zone' => $validated['delivery_zone'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $validated['payment_method'],
                'payment_trx_id' => $isMobilePayment ? $validated['payment_trx_id'] : null,
                'payment_sender_number' => $isMobilePayment ? $validated['payment_sender_number'] : null,
                'payment_verified' => false,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'line_total' => $item['price'] * $item['quantity'],
                ]);
            }

            return $order;
        });

        Session::forget('cart');

        // অসম্পূর্ণ চেকআউট তালিকায় থাকলে "অর্ডার হয়েছে" চিহ্নিত করা (ব্যর্থ হলেও অর্ডার আটকায় না)
        try {
            AbandonedCheckout::whereNull('recovered_at')
                ->where(function ($q) use ($order) {
                    $q->where('session_id', Session::getId());
                    if ($phone = AbandonedCheckout::normalizePhone($order->customer_phone)) {
                        $q->orWhere('customer_phone', $phone);
                    }
                })
                ->update(['recovered_at' => now(), 'order_id' => $order->id]);
        } catch (\Throwable $e) {
            report($e);
        }

        // IP থেকে লোকেশন বের করা (রেসপন্সের পরে, চেকআউট ধীর করে না)
        LookupOrderIpLocation::dispatchAfterResponse($order->id);

        // নতুন অর্ডারে অ্যাডমিন ড্যাশবোর্ডে 🔔 নোটিফিকেশন পাঠানো।
        // এখানে কোনো সমস্যা হলেও কাস্টমারের অর্ডার যেন কখনো আটকে না যায়, তাই try/catch।
        try {
            $admins = User::where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::make()
                    ->title('🛒 নতুন অর্ডার এসেছে!')
                    ->body($order->order_number . ' — ' . $order->customer_name . ' (' . $order->customer_phone . ') — ৳' . number_format($order->total_amount, 0))
                    ->icon('heroicon-o-shopping-cart')
                    ->iconColor('success')
                    ->actions([
                        NotificationAction::make('view')
                            ->label('অর্ডার দেখুন')
                            ->button()
                            ->url(\App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order])),
                    ])
                    ->sendToDatabase($admins);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 📱 Telegram এ মোবাইল পপআপ (রেসপন্সের পরে চলে, চেকআউট ধীর করে না;
        // ব্যর্থ হলেও TelegramService নিজে try/catch করে, অর্ডার আটকায় না)।
        // store() প্রতি অর্ডারে একবারই চলে, তাই একটা অর্ডারে একটাই মেসেজ যাবে।
        dispatch(fn () => TelegramService::notifyNewOrder($order))->afterResponse();

        // 📩📞 কাস্টমারকে কনফার্মেশন SMS / কল (সেটিংসে চালু ও API দেওয়া থাকলে)
        dispatch(fn () => \App\Services\OrderAlertService::notifyNewOrder($order))->afterResponse();

        // Purchase — the single most important event for Facebook's ad algorithm:
        // it's what "Conversions" campaigns optimize toward, so accurate value +
        // strong matching data (hashed phone) here directly lowers cost-per-sale.
        // Fired ONCE here (store() only runs once per real order — refreshing the
        // success page afterwards won't re-fire it). The server (CAPI) call goes
        // out now; the matching browser (Pixel) call is flashed into the session
        // and fires on the success page itself, both tagged with the same
        // $fbEventId so Meta merges them into one event instead of two.
        $fb = app(FacebookConversionsService::class);

        if ($fb->isEnabled()) {
            $fbEventId = (string) Str::uuid();

            $customData = [
                'content_ids' => $order->items->pluck('product_id')->map(fn ($id) => (string) $id)->values()->all(),
                'content_type' => 'product',
                'num_items' => (int) $order->items->sum('quantity'),
                'value' => (float) $order->total_amount,
                'currency' => 'BDT',
            ];

            $userData = $fb->buildUserDataFromRequest($request, [
                // Hashed customer phone — the single biggest Event Match Quality
                // booster available for a COD store with no customer email/login.
                'ph' => FacebookConversionsService::hash(
                    FacebookConversionsService::normalizeBdPhone($order->customer_phone)
                ),
            ]);

            SendFacebookConversionEvent::dispatch(
                'Purchase',
                $fbEventId,
                $customData,
                $userData,
                route('checkout.success', $order->order_number),
            )->afterResponse();

            Session::push('fb_pending_events', [
                'name' => 'Purchase',
                'event_id' => $fbEventId,
                'data' => $customData,
            ]);
        }

        return redirect()
            ->route('checkout.success', $order->order_number)
            ->with('success', 'আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে!');
    }

    public function success(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }
}
