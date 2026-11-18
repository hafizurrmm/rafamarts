<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function show(string $slug)
    {
        // অ্যাডমিন লগইন থাকলে ড্রাফট পেজও প্রিভিউ করা যায় (উপরে হলুদ বার দেখায়)
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        $query = Page::where('slug', $slug);

        if (! $isAdmin) {
            $query->where('is_published', true);
        }

        $page = $query->firstOrFail();

        // HomeController এর একই ক্যাশ — মেনু বানানো না থাকলে হেডারে ক্যাটাগরি মেনু দেখানোর জন্য
        $categories = Category::hydrate(Cache::remember('home:categories', now()->addHours(6), function () {
            return Category::where('is_active', true)->orderBy('sort_order')->get()->toArray();
        }));

        return view('pages.show', compact('page', 'categories', 'isAdmin'));
    }
}
