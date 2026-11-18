<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * চেকআউট ফর্মে ফোন নম্বর দিয়ে অর্ডার না করে চলে যাওয়া কাস্টমার।
 * অর্ডার সম্পন্ন হলে recovered_at বসে যায়।
 */
class AbandonedCheckout extends Model
{
    protected $fillable = [
        'session_id', 'customer_name', 'customer_phone', 'customer_address', 'delivery_zone',
        'cart', 'total_amount', 'customer_ip', 'last_seen_at', 'contacted_at', 'recovered_at', 'order_id',
    ];

    protected $casts = [
        'cart' => 'array',
        'total_amount' => 'decimal:2',
        'last_seen_at' => 'datetime',
        'contacted_at' => 'datetime',
        'recovered_at' => 'datetime',
    ];

    /** বাংলা সংখ্যা/+880 ইত্যাদি সরিয়ে 01XXXXXXXXX আকারে আনে; ভুল হলে null */
    public static function normalizePhone(?string $phone): ?string
    {
        $phone = strtr((string) $phone, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '880')) {
            $digits = '0' . substr($digits, 3);
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) ? $digits : null;
    }

    public function whatsappUrl(): string
    {
        return 'https://wa.me/880' . substr($this->customer_phone, 1);
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->recovered_at !== null => 'অর্ডার হয়েছে',
            $this->contacted_at !== null => 'যোগাযোগ করা হয়েছে',
            default => 'নতুন',
        };
    }
}
