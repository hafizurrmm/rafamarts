<?php

namespace App\Http\Controllers;

use App\Jobs\SendFacebookConversionEvent;
use App\Models\Product;
use App\Services\FacebookConversionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function show(string $slug, Request $request, FacebookConversionsService $fb)
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // ক্যাটাগরি থাকলে একই ক্যাটাগরির প্রোডাক্ট; না থাকলে সর্বশেষ প্রোডাক্ট দেখাবে
        $related = Product::query()
            ->when($product->category_id, fn ($q, $cat) => $q->where('category_id', $cat), fn ($q) => $q->latest())
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        // ViewContent — one of Meta's "standard events" for ecommerce. Sent both to the
        // browser Pixel (see products/show.blade.php) and here, server-side, to the
        // Conversions API, tagged with the same $fbEventId so Meta merges the two into
        // one richer event instead of counting it twice.
        $fbEventId = (string) Str::uuid();

        if ($fb->isEnabled()) {
            SendFacebookConversionEvent::dispatch(
                'ViewContent',
                $fbEventId,
                [
                    'content_ids' => [(string) $product->id],
                    'content_name' => $product->name,
                    'content_type' => 'product',
                    'value' => (float) $product->price,
                    'currency' => 'BDT',
                ],
                $fb->buildUserDataFromRequest($request),
                $request->fullUrl(),
            )->afterResponse();
        }

        return view('products.show', compact('product', 'related', 'fbEventId'));
    }
}
