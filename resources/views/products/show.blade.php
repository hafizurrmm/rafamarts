@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->description ?? $product->name), 160))
@section('meta_image', $product->image_url)

@section('content')
    @php
        $hotline = \App\Models\Setting::get('hotline_number');
        $returnPolicy = \App\Models\Setting::get('product_return_policy');
        $zoneLabels = [
            'inside_dhaka' => 'ঢাকার ভিতরে ডেলিভারি খরচ',
            'near_dhaka' => 'ঢাকার পার্শ্ববর্তী ডেলিভারি খরচ',
            'outside_dhaka' => 'ঢাকার বাইরের ডেলিভারি খরচ',
        ];
    @endphp

    <style>
        /* বর্ণনা/রিটার্ন পলিসির রিচ টেক্সট স্টাইল (Tailwind CDN এ typography প্লাগইন নেই) */
        .rich-content { line-height: 1.8; color: #374151; overflow-wrap: anywhere; }
        .rich-content h1, .rich-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.25rem 0 .5rem; color: #111827; }
        .rich-content h3 { font-size: 1.2rem; font-weight: 700; margin: 1rem 0 .4rem; color: #111827; }
        .rich-content p { margin: .6rem 0; }
        .rich-content ul { list-style: disc; padding-left: 1.5rem; margin: .6rem 0; }
        .rich-content ol { list-style: decimal; padding-left: 1.5rem; margin: .6rem 0; }
        .rich-content a { color: var(--brand-primary); text-decoration: underline; }
        .rich-content img { max-width: 100%; height: auto; border-radius: .5rem; margin: .5rem 0; }
        .rich-content blockquote { border-left: 4px solid var(--brand-primary); padding: .25rem 1rem; color: #4b5563; margin: .8rem 0; }
        .rich-content table { border-collapse: collapse; width: 100%; }
        .rich-content td, .rich-content th { border: 1px solid #e5e7eb; padding: .4rem .6rem; }
    </style>

    <div class="grid md:grid-cols-12 gap-6 md:gap-8 items-start">
        {{-- বামে ছবি: ৫ কলাম --}}
        <div class="md:col-span-5">
            @php
                $imgs = $product->all_image_urls;
                $video = trim((string) $product->video_url);
                $ytId = null;
                if ($video !== '' && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([\w-]{11})~', $video, $m)) {
                    $ytId = $m[1];
                }
            @endphp
            <div class="bg-white border rounded-lg p-2 shadow-sm" id="pgal">
                <div class="relative">
                    <img id="pgal-main" src="{{ $imgs[0] }}" alt="{{ $product->name }}" class="w-full rounded-lg object-cover aspect-square bg-gray-50" fetchpriority="high">
                    @if(count($imgs) > 1)
                        <button type="button" id="pgal-prev" aria-label="আগের ছবি" class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow border flex items-center justify-center text-gray-700 hover:bg-white">‹</button>
                        <button type="button" id="pgal-next" aria-label="পরের ছবি" class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 shadow border flex items-center justify-center text-gray-700 hover:bg-white">›</button>
                    @endif
                </div>
                @if(count($imgs) > 1 || $ytId)
                    <div class="flex gap-2 mt-2 overflow-x-auto pb-1" id="pgal-thumbs">
                        @foreach($imgs as $i => $u)
                            <button type="button" data-src="{{ $u }}" data-i="{{ $i }}"
                                    class="pgal-t shrink-0 w-16 h-16 rounded border-2 overflow-hidden bg-gray-50 {{ $i === 0 ? 'border-[var(--brand-primary)]' : 'border-gray-200' }}">
                                <img src="{{ $u }}" alt="" class="w-full h-full object-cover" loading="lazy">
                            </button>
                        @endforeach
                        @if($ytId)
                            <a href="{{ $video }}" target="_blank" rel="noopener"
                               class="shrink-0 w-16 h-16 rounded border-2 border-gray-200 overflow-hidden relative bg-black">
                                <img src="https://img.youtube.com/vi/{{ $ytId }}/mqdefault.jpg" alt="ভিডিও" class="w-full h-full object-cover opacity-80" loading="lazy">
                                <span class="absolute inset-0 flex items-center justify-center text-white text-xl">▶</span>
                            </a>
                        @endif
                    </div>
                @endif
                @if($video !== '' && ! $ytId)
                    <a href="{{ $video }}" target="_blank" rel="noopener" class="inline-block mt-2 text-sm brand-text underline">▶ ভিডিও দেখুন</a>
                @endif
            </div>
            @if(count($imgs) > 1)
            <script>
            (function () {
                var main = document.getElementById('pgal-main');
                var thumbs = Array.prototype.slice.call(document.querySelectorAll('.pgal-t'));
                var cur = 0;
                function show(i) {
                    cur = (i + thumbs.length) % thumbs.length;
                    main.src = thumbs[cur].getAttribute('data-src');
                    thumbs.forEach(function (t, k) {
                        t.classList.toggle('border-[var(--brand-primary)]', k === cur);
                        t.classList.toggle('border-gray-200', k !== cur);
                    });
                }
                thumbs.forEach(function (t, k) { t.addEventListener('click', function () { show(k); }); });
                document.getElementById('pgal-prev').addEventListener('click', function () { show(cur - 1); });
                document.getElementById('pgal-next').addEventListener('click', function () { show(cur + 1); });
                var x0 = null;
                main.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, {passive: true});
                main.addEventListener('touchend', function (e) {
                    if (x0 === null) return;
                    var dx = e.changedTouches[0].clientX - x0;
                    if (Math.abs(dx) > 40) show(cur + (dx < 0 ? 1 : -1));
                    x0 = null;
                });
            })();
            </script>
            @endif
        </div>

        {{-- ডানে বিবরণ: ৭ কলাম --}}
        <div class="md:col-span-7 bg-white rounded-lg shadow-sm p-5">
            <div class="flex items-start justify-between gap-3">
                <h1 class="text-2xl font-bold mb-2 leading-snug">{{ $product->name }}</h1>
                @if(\App\Support\Ui::on('wishlist'))
                <button type="button" class="wl-btn shrink-0 w-10 h-10 rounded-full border flex items-center justify-center text-gray-500 hover:text-rose-600" data-id="{{ $product->id }}" aria-label="পছন্দের তালিকায় যোগ করুন"><svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg></button>
                @endif
            </div>

            <div class="mb-4">
                <span class="text-2xl text-red-600 font-bold">৳ {{ number_format($product->price, 0) }}</span>
                @if($product->compare_price)
                    <span class="text-gray-400 line-through ml-2">৳ {{ number_format($product->compare_price, 0) }}</span>
                @endif
            </div>

            {{-- "অর্ডার করুন" সরাসরি চেকআউটে নিয়ে যায় (buy_now=1) --}}
            <form action="{{ route('cart.add', $product) }}" method="POST" class="mb-3">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="buy_now" value="1">
                <button type="submit" class="brand-bg w-full text-white font-semibold px-6 py-2.5 rounded hover:opacity-90 transition">
                    🛍️ অর্ডার করুন
                </button>
            </form>

            @if($hotline)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}"
                   class="flex items-center justify-center gap-2 w-full text-center text-white font-semibold px-6 py-2.5 rounded hover:opacity-90 transition mb-4"
                   style="background-color:#000">
                    <span>অর্ডার করতে কল করুন</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 brand-text shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>
                    <span>{{ $hotline }}</span>
                </a>
            @endif

            @if($product->sku)
                <p class="mb-4 text-sm"><span class="font-bold">Code :</span> {{ $product->sku }}</p>
            @endif

            <table class="w-full text-sm">
                @foreach(\App\Models\Order::DELIVERY_CHARGES as $zone => $charge)
                    <tr class="border-b last:border-b-0">
                        <td class="py-2.5 pr-3">{{ $zoneLabels[$zone] ?? $zone }}</td>
                        <td class="py-2.5 text-right font-bold">৳ {{ number_format($charge, 0) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    {{-- নিচে বড় বর্ণনা ও রিটার্ন পলিসি ট্যাব --}}
    <div class="mt-8" id="product-tabs">
        <div class="flex gap-1" role="tablist">
            <button type="button" data-tab="desc" role="tab" class="ptab brand-bg text-white px-5 py-2.5 font-semibold border brand-border rounded-t">Description</button>
            <button type="button" data-tab="return" role="tab" class="ptab bg-white text-gray-800 px-5 py-2.5 font-semibold border rounded-t">Return Policy</button>
        </div>
        <div class="bg-white border rounded-b rounded-tr p-5 md:p-6">
            <div id="tab-desc" class="rich-content">
                @php $descHtml = \App\Support\SafeHtml::clean($product->description); @endphp
                @if($descHtml !== '')
                    {!! $descHtml !!}
                @else
                    <p class="text-gray-500">এই প্রোডাক্টের বিস্তারিত বর্ণনা এখনো যোগ করা হয়নি।</p>
                @endif
            </div>
            <div id="tab-return" class="rich-content hidden">
                @if(filled($returnPolicy))
                    {!! \App\Support\SafeHtml::clean($returnPolicy) !!}
                @else
                    <p>পণ্য হাতে পাওয়ার ৩ দিনের মধ্যে ভুল বা ত্রুটিপূর্ণ পণ্য পেলে আমাদের জানান। যাচাই করে পণ্য বদলে দেওয়া হবে বা টাকা ফেরত দেওয়া হবে।</p>
                    <p>ডেলিভারির সময় পণ্য দেখে নিন। ব্যবহার করা বা প্যাকেট খোলা পণ্য ফেরত নেওয়া হবে না।</p>
                    <p>বিস্তারিত জানতে আমাদের হটলাইনে যোগাযোগ করুন।</p>
                @endif
            </div>
        </div>
    </div>

    <script>
        (function () {
            const root = document.getElementById('product-tabs');
            if (!root) return;
            const buttons = root.querySelectorAll('.ptab');
            buttons.forEach(btn => btn.addEventListener('click', () => {
                buttons.forEach(b => {
                    const active = b === btn;
                    b.classList.toggle('brand-bg', active);
                    b.classList.toggle('brand-border', active);
                    b.classList.toggle('text-white', active);
                    b.classList.toggle('bg-white', !active);
                    b.classList.toggle('text-gray-800', !active);
                    document.getElementById('tab-' + b.dataset.tab).classList.toggle('hidden', !active);
                });
            }));
        })();
    </script>

    @push('fb_page_events')
        fbq('track', 'ViewContent', {!! json_encode([
            'content_ids' => [(string) $product->id],
            'content_name' => $product->name,
            'content_type' => 'product',
            'value' => (float) $product->price,
            'currency' => 'BDT',
        ]) !!}, {eventID: '{{ $fbEventId }}'});
    @endpush

    @if($related->count())
        <div class="mt-10">
            <h2 class="text-xl font-bold mb-4">সম্পর্কিত প্রোডাক্ট</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($related as $item)
                    @include('home.partials.product-card', ['product' => $item])
                @endforeach
            </div>
        </div>
    @endif
@endsection
