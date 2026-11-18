<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When someone arrives from a Facebook/Instagram ad, the link carries an
 * `?fbclid=...` parameter. The Pixel JS normally turns this into an `_fbc`
 * cookie automatically — but if the Pixel script is slow to load, blocked by an
 * ad-blocker, or the visitor lands on a page before JS runs, that cookie can be
 * missing. Since `_fbc` is one of the strongest match-quality signals for the
 * Conversions API (it ties a server-side event straight back to the ad/click
 * that caused it), we also set it server-side as a safety net, in Meta's
 * documented format: fb.{subdomain_index}.{creation_time}.{fbclid}.
 *
 * Reference: https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/fbp-and-fbc
 */
class CaptureFacebookClickId
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->filled('fbclid') && !$request->cookie('_fbc')) {
            $fbc = 'fb.1.' . now()->timestamp . '.' . $request->query('fbclid');
            // 90 days — matches the lifetime Meta's own pixel uses for _fbp/_fbc.
            $response->headers->setCookie(
                cookie('_fbc', $fbc, 60 * 24 * 90)
            );
        }

        return $response;
    }
}
