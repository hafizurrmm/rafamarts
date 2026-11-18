<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // The category list and flash-sale picks are shown on every single homepage
        // request (unfiltered) and rarely change between admin edits, so caching them
        // avoids re-running the same two queries on every visit. Both caches are
        // cleared immediately by Category/Product model events (see their booted()
        // methods) the moment an admin saves a change — so this is free performance,
        // not stale data.
        //
        // IMPORTANT: we cache ->toArray() (plain arrays/scalars) here, never the raw
        // Eloquent Collection/Model objects. Caching real model objects means PHP has
        // to unserialize() a full class instance on every read, and in some local
        // environments that was intermittently coming back as a broken
        // "__PHP_Incomplete_class" (the "incomplete object ... Eloquent\Collection"
        // error on the homepage) — happened with both the file AND database cache
        // drivers, so it wasn't a storage-driver problem, it was caching objects at
        // all. A plain array can never produce that error. Category::hydrate()/
        // Product::hydrate() then rebuild real, fully-working model instances from
        // that cached array, entirely in memory, with zero unserialize() involved.
        $categoriesData = Cache::remember('home:categories', now()->addHours(6), function () {
            return Category::where('is_active', true)->orderBy('sort_order')->get()->toArray();
        });
        $categories = Category::hydrate($categoriesData);

        $flashSaleData = Cache::remember('home:flash_sale', now()->addHours(6), function () {
            return Product::where('is_active', true)
                ->where('is_flash_sale', true)
                ->latest()
                ->take(10)
                ->get()
                ->toArray();
        });
        $flashSaleProducts = Product::hydrate($flashSaleData);

        $productsQuery = Product::where('is_active', true)->latest();

        if ($request->filled('category')) {
            $productsQuery->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('q')) {
            $productsQuery->where('name', 'like', '%' . $request->q . '%');
        }

        // Products per page = columns × rows, both configurable from Store Settings > থিম ও টাইপোগ্রাফি
        $perPage = (int) Setting::get('all_products_columns', 5) * (int) Setting::get('all_products_rows', 3);

        $products = $productsQuery->paginate(max(1, $perPage));

        return view('home.index', compact('categories', 'flashSaleProducts', 'products'));
    }
}
