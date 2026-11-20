<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('site_name', 'ShopBD') . ' - Shop More, Live Better')</title>

    {{-- SEO / social preview tags — these feed the title, description and image Facebook
         (and Google) show when a product link is shared or clicked from an ad, which
         affects click-through and perceived relevance/quality as much as the ad itself. --}}
    @php
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: \App\Models\Setting::get('site_description', 'Shop More, Live Better — ' . \App\Models\Setting::get('site_name', 'ShopBD') . ' থেকে সেরা দামে কেনাকাটা করুন।');
        $metaImage = trim($__env->yieldContent('meta_image')) ?: null;
        $siteLogoForMeta = \App\Models\Setting::get('site_logo');
        $ogImage = $metaImage ?: ($siteLogoForMeta ? asset('storage/' . $siteLogoForMeta) : null);
    @endphp
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', \App\Models\Setting::get('site_name', 'ShopBD'))">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    @php
        $siteFavicon = \App\Models\Setting::get('site_favicon');
        $siteLogoForFavicon = \App\Models\Setting::get('site_logo');
        $faviconPath = $siteFavicon ?? $siteLogoForFavicon;
    @endphp
    @if($faviconPath)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconPath) }}">
    @else
        {{-- Fallback favicon (a simple shopping-bag emoji) so the tab never shows a broken/missing icon
             before a logo/favicon is uploaded from Store Settings --}}
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛍️</text></svg>">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Swiper (used for category slider, flash sale slider, and banner slider) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" defer></script>

    @php
        $primaryColor = \App\Models\Setting::get('primary_color', '#16a34a');
        $secondaryColor = \App\Models\Setting::get('secondary_color', '#0f172a');
        $bodyBg = \App\Models\Setting::get('body_bg_color', '#f9fafb');
        if (! preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $bodyBg)) { $bodyBg = '#f9fafb'; }
        $productImageHeight = \App\Models\Setting::get('product_image_height', '18rem');
        $productTitleSize = \App\Models\Setting::get('product_title_font_size', '0.875rem');
        $productPriceSize = \App\Models\Setting::get('product_price_font_size', '1rem');
        $productButtonSize = \App\Models\Setting::get('product_button_font_size', '0.875rem');
        $productComparePriceSize = \App\Models\Setting::get('product_compare_price_font_size', '0.75rem');
        $sectionHeadingSize = \App\Models\Setting::get('section_heading_font_size', '1.5rem');
    @endphp

    <style>
        body { font-family: 'Hind Siliguri', sans-serif; }

        :root {
            --brand-primary: {{ $primaryColor }};
            --brand-secondary: {{ $secondaryColor }};
            --body-bg: {{ $bodyBg }};
            --product-image-height: {{ $productImageHeight }};
            --product-title-size: {{ $productTitleSize }};
            --product-price-size: {{ $productPriceSize }};
            --product-button-size: {{ $productButtonSize }};
            --product-compare-price-size: {{ $productComparePriceSize }};
            --section-heading-size: {{ $sectionHeadingSize }};
        }

        .site-body { background-color: var(--body-bg) !important; }
        .brand-bg { background-color: var(--brand-primary) !important; }
        .brand-bg-secondary { background-color: var(--brand-secondary) !important; }
        .brand-text { color: var(--brand-primary) !important; }
        .brand-border { border-color: var(--brand-primary) !important; }
        .brand-hover-text:hover { color: var(--brand-primary) !important; }
        .product-image { height: var(--product-image-height); width: 100%; object-fit: cover; }
        .product-title { font-size: calc(var(--product-title-size) * 0.9); line-height: 1.35; }
        .product-price { font-size: calc(var(--product-price-size) * 0.95); line-height: 1.2; }
        .product-button { font-size: calc(var(--product-button-size) * 0.9); }
        .product-compare-price { font-size: var(--product-compare-price-size); }
        @media (max-width: 640px) {
            .product-title { font-size: calc(var(--product-title-size) * 0.82); }
            .product-price { font-size: calc(var(--product-price-size) * 0.88); }
            .product-button { font-size: calc(var(--product-button-size) * 0.82); }
        }
        .section-heading { font-size: var(--section-heading-size); }
    </style>

    @if(\App\Models\Setting::get('fb_enabled', false))
        <link rel="preconnect" href="https://connect.facebook.net">
    @endif
    @include('partials.facebook-pixel')
</head>
<body class="site-body text-gray-800">

    @php
        $siteName = \App\Models\Setting::get('site_name', 'ShopBD');
        $siteLogo = \App\Models\Setting::get('site_logo');
        $hotline = \App\Models\Setting::get('hotline_number');
        $whatsapp = \App\Models\Setting::get('whatsapp_number');
        $facebookUrl = \App\Models\Setting::get('facebook_url');

        // Admin > Website > Menus থেকে বানানো মেনু। হেডার মেনু না থাকলে আগের মতো ক্যাটাগরিগুলোই নেভিগেশনে দেখায়।
        $navItems = \App\Models\Menu::tree('header');

        // শুধু HomeController/PageController $categories পাঠাত, তাই প্রোডাক্ট/কার্ট/চেকআউট পেজে মেনু বার ফাঁকা থাকত।
        // এখন লেআউট নিজেই (একই ক্যাশ থেকে, প্লেইন অ্যারে হিসেবে) ক্যাটাগরি এনে নেয়।
        if (empty($navItems) && ! isset($categories)) {
            try {
                $categories = \App\Models\Category::hydrate(\Illuminate\Support\Facades\Cache::remember('home:categories', now()->addHours(6), function () {
                    return \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get()->toArray();
                }));
            } catch (\Throwable $e) {
                $categories = collect();
            }
        }

        if (empty($navItems)) {
            $navItems = collect($categories ?? [])->map(fn ($c) => [
                'label' => $c->name,
                'url' => route('home', ['category' => $c->slug]),
                'new_tab' => false,
                'children' => [],
            ])->all();
        }
        $navHasChildren = collect($navItems)->contains(fn ($i) => !empty($i['children']));
        $footerMenu = \App\Models\Menu::tree('footer');
    @endphp

    {{-- Top header: logo, search, phone, cart --}}
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            {{-- মোবাইলে হ্যামবার্গার মেনু বাটন (ক্লিক করলে বাম পাশ থেকে ক্যাটাগরি মেনু খোলে) --}}
            <button type="button" id="mobile-menu-open" class="md:hidden -ml-1 p-1 text-gray-700 shrink-0" aria-label="মেনু খুলুন">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                @if($siteLogo)
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain">
                @else
                    <span class="text-2xl font-extrabold text-slate-800">{{ $siteName }}</span>
                @endif
            </a>

            {{-- সার্চ বার (ডেস্কটপ): গোল কোণা, ডানে সবুজ সার্চ বাটন --}}
            <form action="{{ route('home') }}" method="GET" class="flex-1 max-w-2xl hidden md:flex">
                <div class="flex w-full rounded-xl border border-gray-300 bg-white overflow-hidden focus-within:ring-2 focus-within:border-transparent"
                     style="--tw-ring-color: var(--brand-primary)">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="পণ্য, ব্র্যান্ড এবং আরও খুঁজুন..."
                           class="flex-1 min-w-0 px-4 py-2.5 text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent">
                    <button type="submit" class="brand-bg text-white px-5 flex items-center justify-center hover:opacity-90" aria-label="সার্চ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-5">
                @if($hotline)
                    {{-- ক্লিক করলে সরাসরি কল ওপেন হবে (মোবাইলে ডায়ালার) --}}
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="hidden sm:flex items-center gap-2 text-sm hover:opacity-80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 brand-text shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>
                        <div>
                            <div class="text-gray-500 leading-none">অর্ডার করতে কল করুন</div>
                            <div class="text-black font-bold">{{ $hotline }}</div>
                        </div>
                    </a>
                    {{-- মোবাইলে জায়গা কম, তাই শুধু ফোন আইকন --}}
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="sm:hidden brand-text" aria-label="কল করুন"><svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg></a>
                @endif
                {{-- কার্ট: সবুজ কার্ট আইকন + গোল ব্যাজ, নিচে লেখা --}}
                <a href="{{ route('cart.index') }}" class="flex flex-col items-center leading-tight hover:opacity-80">
                    <span class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 brand-text" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.705 2.602-7.189.077-.316-.173-.611-.498-.611H5.106M7.5 14.25L5.106 5.272M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                        <span class="absolute -top-2 -right-3 brand-bg text-white text-[11px] font-bold rounded-full min-w-[1.25rem] h-5 px-1 flex items-center justify-center">
                            {{ collect(session('cart', []))->sum('quantity') }}
                        </span>
                    </span>
                    <span class="text-xs font-semibold text-gray-700 mt-1">কার্ট</span>
                </a>
            </div>
        </div>

        {{-- মোবাইল সার্চ বার (ডেস্কটপে উপরের হেডারে আছে) --}}
        <form action="{{ route('home') }}" method="GET" class="md:hidden px-4 pb-3">
            <div class="flex w-full rounded-xl border border-gray-300 bg-white overflow-hidden">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="পণ্য, ব্র্যান্ড এবং আরও খুঁজুন..."
                       class="flex-1 min-w-0 px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent">
                <button type="submit" class="brand-bg text-white px-4 flex items-center justify-center" aria-label="সার্চ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                    </svg>
                </button>
            </div>
        </form>

        {{-- Category nav bar --}}
        <nav class="brand-bg-secondary hidden md:block">
            {{-- ড্রপডাউন (সাবমেনু) থাকলে overflow-x-auto বাদ, নইলে ড্রপডাউন কেটে যায় --}}
            <div class="max-w-7xl mx-auto px-4 flex items-center gap-x-6 text-sm {{ $navHasChildren ? 'flex-wrap' : 'overflow-x-auto' }}">
                @foreach($navItems as $item)
                    @if(!empty($item['children']))
                        <div class="relative group">
                            <a href="{{ $item['url'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif
                               class="text-white py-3 whitespace-nowrap hover:opacity-80 transition inline-flex items-center gap-1">
                                {{ $item['label'] }} <span class="text-[10px]">▾</span>
                            </a>
                            <div class="absolute left-0 top-full hidden group-hover:block group-focus-within:block bg-white text-gray-800 shadow-lg rounded-b min-w-[12rem] z-50 py-1">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" @if($child['new_tab']) target="_blank" rel="noopener" @endif
                                       class="block px-4 py-2 whitespace-nowrap hover:bg-gray-100">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif
                           class="text-white py-3 whitespace-nowrap hover:opacity-80 transition">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    </header>

    {{-- মোবাইল ক্যাটাগরি ড্রয়ার (হ্যামবার্গার আইকনে ক্লিক করলে খোলে) --}}
    <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-[60] hidden md:hidden"></div>
    <aside id="mobile-menu" class="fixed top-0 left-0 h-full w-72 max-w-[85%] bg-white z-[70] shadow-xl overflow-y-auto md:hidden
                                   transform -translate-x-full transition-transform duration-300" aria-hidden="true">
        <div class="p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <span class="font-bold text-lg text-gray-900">মেনু</span>
                <button type="button" id="mobile-menu-close" class="p-1 text-gray-500 hover:text-gray-800" aria-label="মেনু বন্ধ করুন">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('home') }}" method="GET" class="mt-4 mb-2">
                <div class="flex items-center border border-gray-200 rounded">
                    <input type="text" name="q" placeholder="সার্চ করুন.." class="flex-1 min-w-0 px-3 py-2.5 text-gray-700 placeholder-gray-400 focus:outline-none bg-transparent">
                    <button type="submit" class="px-3 text-gray-700" aria-label="সার্চ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                        </svg>
                    </button>
                </div>
            </form>

            <nav>
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif
                       class="block py-3 border-b border-gray-100 font-medium text-gray-600 hover:text-gray-900 uppercase tracking-wide text-[15px]">
                        {{ $item['label'] }}
                    </a>
                    {{-- মোবাইলে সাবমেনু আইটেমগুলো নিচে একটু ভেতরে সরিয়ে দেখায় --}}
                    @foreach($item['children'] as $child)
                        <a href="{{ $child['url'] }}" @if($child['new_tab']) target="_blank" rel="noopener" @endif
                           class="block py-2.5 pl-5 border-b border-gray-100 text-gray-500 hover:text-gray-900 text-[14px]">
                            – {{ $child['label'] }}
                        </a>
                    @endforeach
                @endforeach
            </nav>
        </div>
    </aside>

    {{-- Flash messages --}}
    <div class="max-w-7xl mx-auto px-4">
        @if(session('success'))
            <div class="mt-4 bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-4 bg-red-100 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif
    </div>

    <main class="max-w-7xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    {{-- Floating WhatsApp button --}}
    @if($whatsapp)
        @php
            // wa.me তে দেশের কোড (880) ছাড়া নম্বর কাজ করে না — 01XXXXXXXXX দিলেও নিজে থেকে 8801XXXXXXXXX বানিয়ে নেয়
            $waDigits = preg_replace('/\D/', '', $whatsapp);
            if (str_starts_with($waDigits, '00')) {
                $waDigits = substr($waDigits, 2);
            }
            if (str_starts_with($waDigits, '01') && strlen($waDigits) === 11) {
                $waDigits = '88' . $waDigits;
            } elseif (str_starts_with($waDigits, '1') && strlen($waDigits) === 10) {
                $waDigits = '880' . $waDigits;
            }
        @endphp
        <a href="https://wa.me/{{ $waDigits }}" target="_blank"
           class="fixed bottom-5 right-5 bg-green-500 hover:bg-green-600 text-white rounded-full w-14 h-14 flex items-center justify-center text-2xl shadow-lg z-50">
            💬
        </a>
    @endif

    <footer class="brand-bg-secondary text-gray-300 mt-10">
        <div class="max-w-7xl mx-auto px-4 py-8 text-center text-sm space-y-2">
            @if(!empty($footerMenu))
                <nav class="flex flex-wrap justify-center gap-x-5 gap-y-2 pb-3">
                    @foreach($footerMenu as $item)
                        <a href="{{ $item['url'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif class="hover:text-white">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            @endif
            @if($facebookUrl)
                <div><a href="{{ $facebookUrl }}" target="_blank" class="hover:text-white">📘 Facebook Page</a></div>
            @endif
            <div>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</div>
        </div>
    </footer>

    <script>
        (function () {
            const menu = document.getElementById('mobile-menu');
            const overlay = document.getElementById('mobile-menu-overlay');
            if (!menu || !overlay) return;

            function openMenu() {
                menu.classList.remove('-translate-x-full');
                menu.setAttribute('aria-hidden', 'false');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            function closeMenu() {
                menu.classList.add('-translate-x-full');
                menu.setAttribute('aria-hidden', 'true');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            document.getElementById('mobile-menu-open')?.addEventListener('click', openMenu);
            document.getElementById('mobile-menu-close')?.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
            // ডেস্কটপ সাইজে গেলে ড্রয়ার বন্ধ করে দেয়
            window.addEventListener('resize', function () { if (window.innerWidth >= 768) closeMenu(); });
        })();
    </script>
    @stack('scripts')
</body>
</html>
