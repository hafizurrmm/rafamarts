@extends('layouts.app')

@section('title', \App\Models\Setting::get('site_name', 'ShopBD') . ' - Shop More, Live Better')

@section('content')

    @php
        $isFiltered = request()->filled('q') || request()->filled('category');
        $bannerType = \App\Models\Setting::get('banner_type', 'static'); // static | slider
        $bannerImage = \App\Models\Setting::get('banner_image');
        $bannerImages = \App\Models\Setting::getArray('banner_images');
        $flashSaleColumns = (int) \App\Models\Setting::get('flash_sale_columns', 5);
        $allProductsColumns = (int) \App\Models\Setting::get('all_products_columns', 5);
    @endphp

    @unless($isFiltered)
        {{-- Hero banner: static image, slider of multiple images, or the default gradient fallback --}}
        @if($bannerType === 'slider' && count($bannerImages))
            <div class="mb-10 rounded-xl overflow-hidden">
                <div class="swiper banner-swiper">
                    <div class="swiper-wrapper">
                        @foreach($bannerImages as $index => $img)
                            <div class="swiper-slide">
                                {{-- First slide is above-the-fold (LCP element) — load it eagerly and with
                                     high priority; the rest can wait since they're hidden until swiped to. --}}
                                <img src="{{ asset('storage/' . $img) }}" alt="Banner" class="w-full object-cover"
                                     @if($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        @elseif($bannerType === 'static' && $bannerImage)
            <div class="mb-10 rounded-xl overflow-hidden">
                <a href="#all-products">
                    <img src="{{ asset('storage/' . $bannerImage) }}" alt="Banner" class="w-full object-cover" fetchpriority="high">
                </a>
            </div>
        @elseif($bannerType === 'split' && ($bannerImage || \App\Models\Setting::get('banner_heading')))
            @php
                $hex = fn ($v, $d) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $v) ? $v : $d;
                $bBg = $hex(\App\Models\Setting::get('banner_bg_color'), '#ffffff');
                $bFg = $hex(\App\Models\Setting::get('banner_text_color'), '#0f2a43');
                $bEyebrow = \App\Models\Setting::get('banner_eyebrow');
                $bHeading = \App\Models\Setting::get('banner_heading');
                $bHighlight = \App\Models\Setting::get('banner_heading_highlight');
                $bSub = \App\Models\Setting::get('banner_subtext');
                $bBtn = \App\Models\Setting::get('banner_button_text'); // খালি থাকলে বাটন দেখাবে না
                $bUrl = \App\Models\Setting::get('banner_button_url', '#all-products') ?: '#all-products';
                $isBn = (bool) preg_match('/\p{Bengali}/u', $bHeading . $bHighlight . $bEyebrow);
                if (! preg_match('~^(#|/|https?://)~i', $bUrl)) { $bUrl = '#all-products'; }
            @endphp
            <div class="mb-10 rounded-xl overflow-hidden relative flex items-center min-h-[240px] md:min-h-[445px]" style="background: {{ $bBg }}; color: {{ $bFg }};">
                {{-- ছবি পুরো ব্যানারের ব্যাকগ্রাউন্ডে; লেখা তার উপরে বাম পাশে --}}
                @if($bannerImage)
                    <img src="{{ asset('storage/' . $bannerImage) }}" alt="{{ $bHeading ?: 'Banner' }}" class="absolute inset-0 w-full h-full object-cover object-left" fetchpriority="high">
                @endif
                <div class="relative z-10 py-5 pr-6 pl-[30px] md:py-12 md:pr-12 md:pl-[93px] w-full md:w-1/2">
                    @if($bEyebrow)<div class="text-xs md:text-sm font-bold {{ $isBn ? '' : 'tracking-[0.2em] uppercase' }} brand-text mb-1">{{ $bEyebrow }}</div>@endif
                    <h1 class="text-3xl md:text-[4rem] font-extrabold {{ $isBn ? 'leading-[1.2]' : 'leading-[0.95] tracking-tight' }} mb-2">
                        {{ $bHeading }}@if($bHighlight) <span class="brand-text">{{ $bHighlight }}</span>@endif
                    </h1>
                    @if($bSub)<p class="text-sm md:text-base leading-snug opacity-80 mb-4 max-w-xl">{!! nl2br(e($bSub)) !!}</p>@endif
                    @if($bBtn)
                        <a href="{{ $bUrl }}" class="inline-block brand-bg text-white font-bold px-6 py-3 rounded-full shadow hover:opacity-90 transition">{{ $bBtn }} →</a>
                    @endif
                </div>
            </div>
        @else
            <div class="brand-bg-secondary rounded-xl text-white p-8 md:p-12 mb-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl md:text-5xl font-extrabold mb-2">{{ \App\Models\Setting::get('site_name', 'ShopBD') }}</h1>
                    <p class="text-lg md:text-xl text-gray-200 mb-4">— Shop More, Live Better —</p>
                    <div class="flex flex-wrap gap-4 text-sm">
                        <span>✅ Trusted Quality</span>
                        <span>🚚 Fast Delivery</span>
                        <span>🏆 Best Prices</span>
                        <span>💬 Dedicated Support</span>
                    </div>
                </div>
                <a href="#all-products" class="bg-white brand-text font-bold px-6 py-3 rounded-full shadow hover:bg-gray-50 transition">
                    🛒 SHOP NOW
                </a>
            </div>
        @endif

        {{-- Category slider --}}
        @if($categories->count())
            <div class="mb-10">
                <div class="swiper category-swiper">
                    <div class="swiper-wrapper">
                        @foreach($categories as $category)
                            <div class="swiper-slide text-center">
                                <a href="{{ route('home', ['category' => $category->slug]) }}" class="block bg-white rounded-lg shadow-sm p-4">
                                    <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/placeholder-product.png') }}"
                                         alt="{{ $category->name }}"
                                         class="w-full max-w-[8rem] md:max-w-[9rem] aspect-square mx-auto rounded-full object-cover"
                                         loading="lazy" decoding="async">
                                    <p class="mt-3 text-base font-medium brand-hover-text">{{ $category->name }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Flash Sale slider --}}
        @if($flashSaleProducts->count())
            <div class="mb-10 relative">
                <h2 class="section-heading font-bold mb-4">Flash <span class="brand-text">Sale</span></h2>
                <div class="swiper flash-sale-swiper" data-columns="{{ $flashSaleColumns }}">
                    <div class="swiper-wrapper">
                        @foreach($flashSaleProducts as $product)
                            <div class="swiper-slide h-auto">
                                @include('home.partials.product-card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
                <button class="flash-sale-prev absolute top-1/2 -left-3 z-10 bg-white shadow rounded-full w-9 h-9 flex items-center justify-center">‹</button>
                <button class="flash-sale-next absolute top-1/2 -right-3 z-10 bg-white shadow rounded-full w-9 h-9 flex items-center justify-center">›</button>
            </div>
        @endif
    @endunless

    {{-- All Products / Search & Category results --}}
    <div id="all-products">
        <div class="flex items-center justify-between mb-4">
            <h2 class="section-heading font-bold">
                @if(request()->filled('q'))
                    "{{ request('q') }}" এর সার্চ ফলাফল
                @elseif(request()->filled('category'))
                    {{ $categories->firstWhere('slug', request('category'))?->name ?? 'Category' }}
                @else
                    All <span class="brand-text">Products</span>
                @endif
            </h2>
            <form action="{{ route('home') }}" method="GET">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search..."
                       class="border rounded px-3 py-1.5 text-sm">
            </form>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" style="--all-products-cols: {{ $allProductsColumns }};" id="all-products-grid">
            @forelse($products as $product)
                @include('home.partials.product-card', ['product' => $product])
            @empty
                <p class="col-span-full text-gray-500">কোনো প্রোডাক্ট পাওয়া যায়নি।</p>
            @endforelse
        </div>

        <style>
            @media (min-width: 768px) {
                #all-products-grid { grid-template-columns: repeat(var(--all-products-cols), minmax(0, 1fr)); }
            }
        </style>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('.banner-swiper')) {
            new Swiper('.banner-swiper', {
                loop: true,
                autoplay: { delay: 4000 },
                pagination: { el: '.swiper-pagination', clickable: true },
            });
        }

        if (document.querySelector('.category-swiper')) {
            new Swiper('.category-swiper', {
                spaceBetween: 16,
                // মোবাইলে গোল ছবির সাইজ বড় রেখে একসাথে ২টা ক্যাটাগরি দেখাবে
                slidesPerView: 2,
                loop: true,
                speed: 1000,
                autoplay: {
                    delay: 10000,
                    disableOnInteraction: false,
                },
                breakpoints: {
                    480: { slidesPerView: 3, spaceBetween: 20 },
                    640: { slidesPerView: 5, spaceBetween: 20 },
                    768: { slidesPerView: 6 },
                    1024: { slidesPerView: 6 },
                },
            });
        }

        if (document.querySelector('.flash-sale-swiper')) {
            const flashSaleCols = parseInt(document.querySelector('.flash-sale-swiper').dataset.columns || '5', 10);
            new Swiper('.flash-sale-swiper', {
                spaceBetween: 16,
                slidesPerView: 2,
                navigation: {
                    nextEl: '.flash-sale-next',
                    prevEl: '.flash-sale-prev',
                },
                breakpoints: {
                    480: { slidesPerView: Math.min(2, flashSaleCols) },
                    640: { slidesPerView: Math.min(3, flashSaleCols) },
                    768: { slidesPerView: Math.min(4, flashSaleCols) },
                    1024: { slidesPerView: flashSaleCols },
                },
            });
        }
    });
</script>
@endpush

@endsection
