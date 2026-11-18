<?php

namespace App\Http\Controllers;

use App\Jobs\SendFacebookConversionEvent;
use App\Models\Product;
use App\Services\FacebookConversionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private const SESSION_KEY = 'cart';

    public function index()
    {
        $cart = $this->getCart();

        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, Product $product, FacebookConversionsService $fb)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = (int) $request->input('quantity', 1);
        $cart = $this->getCart();

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url,
                'price' => (float) $product->price,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);

        // AddToCart — fired both server-side (CAPI, right now) and client-side (Pixel,
        // on the NEXT page render — this request redirects, so JS can't fire here).
        // The event is flashed into the session and picked up by
        // partials/facebook-pixel.blade.php on the following page load, tagged with
        // the same $fbEventId so Meta de-dupes the two deliveries into one event.
        if ($fb->isEnabled()) {
            $fbEventId = (string) Str::uuid();
            $customData = [
                'content_ids' => [(string) $product->id],
                'content_name' => $product->name,
                'content_type' => 'product',
                'value' => (float) $product->price * $quantity,
                'currency' => 'BDT',
            ];

            SendFacebookConversionEvent::dispatch(
                'AddToCart',
                $fbEventId,
                $customData,
                $fb->buildUserDataFromRequest($request),
                $request->fullUrl(),
            )->afterResponse();

            Session::push('fb_pending_events', [
                'name' => 'AddToCart',
                'event_id' => $fbEventId,
                'data' => $customData,
            ]);
        }

        // "অর্ডার করুন" (Order Now) buttons submit a buy_now=1 flag — skip the cart
        // page and go straight to checkout. Plain "কার্টে যোগ করুন" buttons don't
        // send this flag, so they just stay on the current page as before.
        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', 'প্রোডাক্টটি কার্টে যোগ হয়েছে।');
    }

    public function update(Request $request, int $productId)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = (int) $request->quantity;
            Session::put(self::SESSION_KEY, $cart);
        }

        return back();
    }

    public function remove(int $productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);

        return back()->with('success', 'প্রোডাক্টটি কার্ট থেকে সরানো হয়েছে।');
    }

    private function getCart(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }
}
