<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * লগইন করা কাস্টমারের Wishlist সার্ভারে সিঙ্ক।
     * mode=merge  → ব্রাউজারের + সার্ভারের তালিকা মিলিয়ে ফেরত দেয়
     * mode=replace → ব্রাউজারের তালিকাটাই সার্ভারে বসায়
     */
    public function sync(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'customer', 403);

        $client = collect((array) $request->input('ids', []))
            ->map(fn ($v) => (int) $v)->filter()->unique()->take(100)->values();

        $server = DB::table('wishlist_items')->where('user_id', $user->id)->pluck('product_id');

        $final = $request->input('mode') === 'replace' ? $client : $client->merge($server)->unique()->values();
        $final = Product::whereIn('id', $final)->pluck('id')->values();

        DB::transaction(function () use ($user, $final) {
            DB::table('wishlist_items')->where('user_id', $user->id)->whereNotIn('product_id', $final)->delete();
            $existing = DB::table('wishlist_items')->where('user_id', $user->id)->pluck('product_id');
            $rows = $final->diff($existing)->map(fn ($id) => ['user_id' => $user->id, 'product_id' => $id, 'created_at' => now(), 'updated_at' => now()])->values()->all();
            if ($rows) {
                DB::table('wishlist_items')->insert($rows);
            }
        });

        return response()->json(['ids' => $final]);
    }
}
