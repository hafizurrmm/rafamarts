<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id',
        'customer_name', 'customer_phone', 'customer_address', 'customer_note', 'customer_ip', 'ip_location', 'ip_country_code', 'ip_isp',
        'sub_total', 'delivery_charge', 'discount', 'total_amount',
        'delivery_zone', 'status', 'payment_status', 'payment_method',
        'payment_trx_id', 'payment_sender_number', 'payment_verified',
        'courier_name', 'consignment_id', 'tracking_code',
        'courier_order_id', 'courier_status', 'courier_response', 'courier_synced_at',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_verified' => 'boolean',
        'courier_synced_at' => 'datetime',
    ];

    public const PAYMENT_LABELS = [
        'cod' => 'Cash on Delivery',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
    ];

    // ম্যানুয়াল (TrxID দিয়ে যাচাই করা) মোবাইল ব্যাংকিং মেথড
    public const MOBILE_PAYMENT_METHODS = ['bkash', 'nagad', 'rocket'];

    /**
     * চেকআউটে এই মুহূর্তে কোন কোন পেমেন্ট মেথড দেখাবে (Store Settings অনুযায়ী)।
     * [key => ['label' => ..., 'number' => ..., 'type' => ...]]
     * কিছুই চালু না থাকলে চেকআউট যেন ভেঙে না যায়, তাই COD ফলব্যাক।
     */
    public static function availablePaymentMethods(): array
    {
        $cod = ['label' => 'ক্যাশ অন ডেলিভারি (COD)', 'number' => null, 'type' => null];
        $methods = [];

        if ((bool) Setting::get('cod_enabled', true)) {
            $methods['cod'] = $cod;
        }

        foreach (self::MOBILE_PAYMENT_METHODS as $key) {
            if (! (bool) Setting::get("{$key}_enabled", false)) {
                continue;
            }

            $number = trim((string) Setting::get("{$key}_number"));

            if ($number === '') {
                continue; // নম্বর না থাকলে কাস্টমার কোথায় টাকা পাঠাবে? তাই দেখাবো না
            }

            $methods[$key] = [
                'label' => self::PAYMENT_LABELS[$key],
                'number' => $number,
                'type' => Setting::get("{$key}_type", 'send_money') === 'payment' ? 'Payment' : 'Send Money',
            ];
        }

        return $methods ?: ['cod' => $cod];
    }

    public function isMobilePayment(): bool
    {
        return in_array($this->payment_method, self::MOBILE_PAYMENT_METHODS, true);
    }

    public function paymentLabel(): string
    {
        return self::PAYMENT_LABELS[$this->payment_method] ?? strtoupper((string) $this->payment_method);
    }

    // Bangla-friendly delivery zone => charge map (matches the screenshot)
    public const DELIVERY_CHARGES = [
        'inside_dhaka' => 80,
        'near_dhaka' => 100,
        'outside_dhaka' => 150,
    ];

    protected static function booted(): void
    {
        // ক্যাশ অন ডেলিভারি: আলাদা পেমেন্ট স্ট্যাটাস ম্যানুয়ালি না বদলে অর্ডার "completed" হলেই paid ধরা হয়।
        // (রেভিনিউ স্ট্যাটস payment_status এর উপর নির্ভর করে, তাই কলামটা ভেতরে সিঙ্ক থাকে।)
        static::saving(function (Order $order) {
            if ($order->isDirty('status') || ! $order->exists) {
                $order->payment_status = $order->status === 'completed' ? 'paid' : 'unpaid';
            }
        });

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Recompute sub_total from current order items, then total_amount
     * from sub_total + delivery_charge - discount. Called automatically
     * whenever an OrderItem is saved/deleted (see OrderItem::booted()),
     * and can also be called manually after editing delivery_charge/discount.
     */
    public function recalculateTotals(): void
    {
        $subTotal = $this->items()->sum('line_total');
        $total = max(0, $subTotal + (float) $this->delivery_charge - (float) $this->discount);

        $this->updateQuietly([
            'sub_total' => $subTotal,
            'total_amount' => $total,
        ]);
    }
}
