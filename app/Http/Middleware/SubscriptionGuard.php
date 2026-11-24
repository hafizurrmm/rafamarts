<?php

namespace App\Http\Middleware;

use App\Support\Subscription;
use Closure;
use Illuminate\Http\Request;

/** সাবস্ক্রিপশনের মেয়াদ ও গ্রেস শেষ হলে অ্যাডমিন এলাকা বন্ধ (স্টোরফ্রন্ট চালু থাকে) */
class SubscriptionGuard
{
    public function handle(Request $request, Closure $next)
    {
        if (Subscription::locked() && ! $request->routeIs('subscription.expired')) {
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
