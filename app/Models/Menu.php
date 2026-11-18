<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    public const LOCATIONS = [
        'header' => 'হেডার মেনু (উপরের নেভিগেশন)',
        'footer' => 'ফুটার মেনু (নিচের লিংক)',
    ];

    protected $fillable = ['name', 'location', 'is_active', 'items'];

    protected $casts = [
        'is_active' => 'boolean',
        'items' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => static::flush());
        static::deleted(fn () => static::flush());
    }

    public static function flush(): void
    {
        foreach (array_keys(self::LOCATIONS) as $location) {
            Cache::forget("menu:{$location}");
        }
    }

    /**
     * লেআউটের জন্য তৈরি মেনু: [['label','url','new_tab','children'=>[...]], ...]
     * মেনু না থাকলে ফাঁকা অ্যারে ফেরত দেয় (তখন লেআউট আগের ক্যাটাগরি মেনুই দেখায়)।
     *
     * ক্যাশে শুধু প্লেইন অ্যারে রাখা হয়, মডেল অবজেক্ট না (README এর "incomplete object" সমস্যা এড়াতে)।
     * টেবিল না থাকলে (মাইগ্রেট করার আগে) বা অন্য কোনো সমস্যায় সাইট যেন কখনো না ভাঙে, তাই try/catch।
     */
    public static function tree(string $location): array
    {
        try {
            return Cache::remember("menu:{$location}", now()->addHour(), fn () => static::buildTree($location));
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected static function buildTree(string $location): array
    {
        $menu = static::where('location', $location)->where('is_active', true)->first();

        if (! $menu || empty($menu->items)) {
            return [];
        }

        $pages = Page::where('is_published', true)->pluck('slug', 'id')->all();
        $categories = Category::where('is_active', true)->pluck('slug', 'id')->all();

        $resolve = function (array $item) use ($pages, $categories): ?array {
            $label = trim((string) ($item['label'] ?? ''));

            if ($label === '') {
                return null;
            }

            $url = match ($item['type'] ?? 'custom') {
                'home' => '/',
                'page' => isset($pages[$item['page_id'] ?? 0]) ? '/' . $pages[$item['page_id']] : null,
                'category' => isset($categories[$item['category_id'] ?? 0]) ? '/?category=' . $categories[$item['category_id']] : null,
                default => trim((string) ($item['url'] ?? '')) ?: '#',
            };

            // মুছে ফেলা/অপ্রকাশিত পেজ বা ক্যাটাগরির লিংক মেনু থেকে বাদ যায়
            if ($url === null) {
                return null;
            }

            // javascript: লিংক কখনোই নয়
            if (preg_match('/^\s*javascript:/i', $url)) {
                $url = '#';
            }

            return [
                'label' => $label,
                'url' => $url,
                'new_tab' => (bool) ($item['new_tab'] ?? false),
                'children' => [],
            ];
        };

        $tree = [];

        foreach (array_values($menu->items) as $item) {
            $node = $resolve($item);

            if (! $node) {
                continue;
            }

            foreach (array_values($item['children'] ?? []) as $child) {
                if ($childNode = $resolve($child)) {
                    $node['children'][] = $childNode;
                }
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
