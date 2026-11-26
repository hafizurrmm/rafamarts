<?php

namespace App\Http\Controllers;

use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Setting;
use App\Support\PageBlocks;
use App\Support\SafeHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LandingPageController extends Controller
{
    private function findPage(string $slug): LandingPage
    {
        $page = LandingPage::with('product')->where('slug', $slug)->firstOrFail();
        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        abort_unless($page->is_published || $isAdmin, 404);
        abort_unless($page->product && $page->product->is_active, 404);

        return $page;
    }

    public function show(string $slug)
    {
        $page = $this->findPage($slug);

        return view('landing.show', [
            'page' => $page,
            'product' => $page->product,
            'bodyHtml' => SafeHtml::clean($page->body),
            'videoEmbed' => PageBlocks::youtubeEmbed($page->video_url),
            'paymentMethods' => Order::availablePaymentMethods(),
            'siteName' => Setting::get('site_name', config('shop.name')),
        ]);
    }

    /** ল্যান্ডিং পেজের ফর্ম থেকে অর্ডার — এই প্রোডাক্টটি কার্টে বসিয়ে স্বাভাবিক চেকআউটের একই নিয়মে (ফ্রড চেক, নোটিফিকেশন, Pixel সহ) অর্ডার নেয় */
    public function order(Request $request, string $slug, CheckoutController $checkout)
    {
        $page = $this->findPage($slug);
        $product = $page->product;

        $request->validate(['quantity' => 'nullable|integer|min:1|max:20']);

        Session::put('cart', [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url,
                'price' => (float) $product->price,
                'quantity' => (int) $request->input('quantity', 1),
            ],
        ]);

        return $checkout->store($request);
    }
}
