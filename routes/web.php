<?php

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Shop front-end (public)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');

// Cart (session based, no login required)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout (matches the screenshot: name, phone, address, note, delivery zone)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/capture', [CheckoutController::class, 'capture'])->middleware('throttle:40,1')->name('checkout.capture');
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

// কাস্টমার একাউন্ট (ফোন + পাসওয়ার্ড)
Route::get('/login', [CustomerAuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login', [CustomerAuthController::class, 'login'])->middleware(['guest', 'throttle:10,1'])->name('login.attempt');
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [CustomerAuthController::class, 'register'])->middleware(['guest', 'throttle:10,1'])->name('register.attempt');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
Route::get('/account', [CustomerAuthController::class, 'account'])->middleware('auth')->name('account.index');
Route::post('/account', [CustomerAuthController::class, 'updateAccount'])->middleware('auth')->name('account.update');

// Wishlist (ব্রাউজারে সেভ; সার্ভার শুধু কার্ড HTML দেয়)
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::get('/wishlist/items', [WishlistController::class, 'items'])->middleware('throttle:60,1')->name('wishlist.items');
Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->middleware(['auth', 'throttle:60,1'])->name('wishlist.sync');

// অর্ডার ট্র্যাকিং (অর্ডার আইডি + ফোন মিলতে হবে)
Route::get('/track-order', [OrderTrackingController::class, 'show'])->name('track.show');
Route::post('/track-order', [OrderTrackingController::class, 'lookup'])->middleware('throttle:15,1')->name('track.lookup');

// Admin — bulk invoice print (opened from the Recent Orders widget / Order list bulk action)
// Protected by the default `auth` middleware here; since only admins can log into Filament,
// this is reasonably safe, but you may want to tighten this further (e.g. a dedicated admin middleware).
Route::view('/subscription-expired', 'subscription.expired')->name('subscription.expired');

Route::middleware(['auth', \App\Http\Middleware\SubscriptionGuard::class, \App\Http\Middleware\AdminOnly::class])->group(function () {
    Route::get('/admin/orders/invoices', [InvoiceController::class, 'index'])->name('admin.orders.invoices');
    Route::get('/admin/orders/invoices/pos', [InvoiceController::class, 'posBulk'])->name('admin.orders.invoices.pos');
    Route::get('/admin/orders/{order}/invoice', [InvoiceController::class, 'view'])->name('admin.orders.invoice.view');
    Route::get('/admin/orders/{order}/invoice/download', [InvoiceController::class, 'download'])->name('admin.orders.invoice.download');
    Route::get('/admin/orders/{order}/invoice/pos', [InvoiceController::class, 'posInvoice'])->name('admin.orders.invoice.pos');
});

// Filament admin panel is auto-registered at /admin by the Filament panel provider.

// Admin > Website > Pages এ বানানো পেজ (যেমন /about-us, /privacy-policy)।
// ⚠️ এই রুটটা অবশ্যই ফাইলের একদম শেষে থাকতে হবে — সব নির্দিষ্ট রুটের পরে, নইলে অন্য URL ধরে ফেলবে।
// regex এর শুরুর অংশ (?!admin$|...) এর কারণে /admin, /cart, /checkout এ এটা কখনো মিলবে না।
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '(?!admin$|cart$|checkout$|product$|login$|livewire$|storage$|up$|track-order$|wishlist$|register$|account$|logout$|install$|subscription-expired$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('pages.show');
