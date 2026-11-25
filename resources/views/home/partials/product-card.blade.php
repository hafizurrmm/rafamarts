<div class="wl-card bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-shadow overflow-hidden relative flex flex-col h-full" data-id="{{ $product->id }}">
    @if($product->discount_percent)
        <span class="absolute top-2 left-2 bg-red-600 text-white text-[11px] font-bold px-2.5 py-0.5 rounded-full z-10 shadow">
            -{{ $product->discount_percent }}%
        </span>
    @endif

    @if(\App\Support\Ui::on('wishlist'))
    <button type="button" class="wl-btn absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-white/90 shadow flex items-center justify-center text-gray-500 hover:text-rose-600" data-id="{{ $product->id }}" aria-label="পছন্দের তালিকায় যোগ করুন">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
    </button>
    @endif

    <a href="{{ route('products.show', $product->slug) }}" class="block">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-image" loading="lazy" decoding="async">
    </a>

    <div class="p-3 md:p-3.5 flex flex-col flex-1">
        <a href="{{ route('products.show', $product->slug) }}" class="product-title font-semibold text-gray-800 line-clamp-2 mb-1.5 brand-hover-text">
            {{ $product->name }}
        </a>

        <div class="mb-2.5 mt-auto flex items-baseline flex-wrap gap-x-2">
            <span class="product-price brand-text font-bold">৳{{ number_format($product->price, 0) }}</span>
            @if($product->compare_price)
                <span class="product-compare-price text-gray-400 line-through">৳{{ number_format($product->compare_price, 0) }}</span>
            @endif
        </div>

        <form action="{{ route('cart.add', $product) }}" method="POST">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="buy_now" value="1">
            <button type="submit" class="w-full brand-bg text-white product-button font-semibold py-2 rounded-full hover:opacity-90 flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.705 2.602-7.189.077-.316-.173-.611-.498-.611H5.106M7.5 14.25L5.106 5.272M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                অর্ডার করুন
            </button>
        </form>
    </div>
</div>
