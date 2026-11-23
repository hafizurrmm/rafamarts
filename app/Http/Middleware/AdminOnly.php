<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** শুধু অ্যাডমিন রোলের ইউজার (কাস্টমার একাউন্ট নয়) */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return $next($request);
    }
}
