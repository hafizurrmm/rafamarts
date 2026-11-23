<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        return view('wishlist.index');
    }

    /** ?ids=1,2,3 → প্রোডাক্ট কার্ডের HTML (সর্বোচ্চ ৫০টি) */
    public function items(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($v) => (int) $v)->filter()->unique()->take(50)->values();

        $products = Product::whereIn('id', $ids)->where('is_active', true)->get()
            ->sortBy(fn ($p) => $ids->search($p->id));

        return view('wishlist.items', ['products' => $products]);
    }
}
