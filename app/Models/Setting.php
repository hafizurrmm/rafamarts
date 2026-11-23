<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    // For settings that store an array (e.g. multiple banner images from a Filament FileUpload)
    public static function getArray(string $key, array $default = []): array
    {
        $raw = static::get($key);

        if (blank($raw)) {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    // Save many key => value pairs at once (used by settings forms)
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::set($key, $value);
        }
    }

    // হোমপেজের ব্যানারের নিচের "ফিচার স্ট্রিপ" — Settings এ কিছু সেভ না করা থাকলে এই ডিফল্ট দেখায়
    public const FEATURE_ICONS = [
        'truck' => 'ট্রাক (ডেলিভারি)',
        'phone' => 'ফোন (সাপোর্ট)',
        'card' => 'কার্ড (পেমেন্ট/দাম)',
        'tag' => 'ট্যাগ (ডিসকাউন্ট)',
        'shield' => 'শিল্ড (নিরাপত্তা)',
        'gift' => 'গিফট',
        'clock' => 'ঘড়ি',
        'heart' => 'হার্ট',
    ];

    public static function featureItems(): array
    {
        $items = array_values(static::getArray('feature_items'));

        return $items ?: [
            ['icon' => 'truck', 'title' => 'Cash On Delivery', 'subtitle' => 'Door to Door Delivery'],
            ['icon' => 'phone', 'title' => 'Online Support 24/7', 'subtitle' => 'Support online 24 hours a day'],
            ['icon' => 'card', 'title' => 'Best Price Guarantee', 'subtitle' => 'Great Quality, Best Price'],
            ['icon' => 'tag', 'title' => 'Exclusive Discount', 'subtitle' => 'Order 2 items get free delivery'],
        ];
    }
}
