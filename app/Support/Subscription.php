<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * সাবস্ক্রিপশনের অবস্থা — .env এর SUBSCRIPTION_EXPIRES_AT থেকে পড়ে (ক্লায়েন্ট বদলাতে পারে না)।
 * status: none (সীমাহীন) | active | expiring (মেয়াদ কাছে) | grace (মেয়াদ শেষ, গ্রেস চলছে) | expired (অ্যাডমিন লক)
 */
class Subscription
{
    public static function state(): array
    {
        $raw = config('shop.subscription.expires_at');

        if (blank($raw)) {
            return ['status' => 'none', 'locked' => false, 'days_left' => null, 'expires_at' => null];
        }

        try {
            $expires = Carbon::parse($raw)->endOfDay();
        } catch (\Throwable $e) {
            // তারিখ ভুল লেখা থাকলে ক্লায়েন্টকে লক না করে সীমাহীন ধরা হবে
            return ['status' => 'none', 'locked' => false, 'days_left' => null, 'expires_at' => null];
        }

        $now = now();
        $grace = max(0, (int) config('shop.subscription.grace_days', 3));
        $warn = max(0, (int) config('shop.subscription.warn_days', 7));

        if ($now->lte($expires)) {
            $left = (int) ceil($now->floatDiffInDays($expires));
            $status = $left <= $warn ? 'expiring' : 'active';

            return ['status' => $status, 'locked' => false, 'days_left' => $left, 'expires_at' => $expires];
        }

        $graceEnds = $expires->copy()->addDays($grace);
        if ($now->lte($graceEnds)) {
            return [
                'status' => 'grace', 'locked' => false,
                'days_left' => -1 * (int) ceil($expires->floatDiffInDays($now)),
                'expires_at' => $expires, 'grace_ends' => $graceEnds,
            ];
        }

        return ['status' => 'expired', 'locked' => true, 'days_left' => null, 'expires_at' => $expires];
    }

    public static function locked(): bool
    {
        return self::state()['locked'];
    }
}
