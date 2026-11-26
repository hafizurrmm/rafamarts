<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $title = $page->headline ?: $product->name;
        $desc = \Illuminate\Support\Str::limit(strip_tags($page->subheadline ?: ($product->description ?? $product->name)), 160);
        $primary = \App\Models\Setting::get('primary_color', '#16a34a');
        if (! preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $primary)) { $primary = '#16a34a'; }
        $heroUrl = $page->hero_image ? asset('storage/' . $page->hero_image) : $product->image_url;
        $favicon = \App\Models\Setting::get('site_favicon') ?? \App\Models\Setting::get('site_logo');
        $logo = \App\Models\Setting::get('site_logo');
        $btn = $page->button_text ?: 'অর্ডার করুন';
        $hotline = \App\Models\Setting::get('hotline_number');
        $selectedPayment = old('payment_method', array_key_first($paymentMethods));
    @endphp
    <title>{{ $title }} — {{ $siteName }}</title>
    <meta name="description" content="{{ $desc }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($heroUrl)<meta property="og:image" content="{{ $heroUrl }}">@endif
    @if(! $page->is_published)<meta name="robots" content="noindex">@endif
    @if($favicon)<link rel="icon" href="{{ asset('storage/' . $favicon) }}">@endif

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand: {{ $primary }}; }
        body { font-family: 'Hind Siliguri', sans-serif; background: #f8fafc; color: #0f172a; }
        .brand-bg { background: var(--brand); }
        .brand-text { color: var(--brand); }
        .brand-border { border-color: var(--brand); }
        .prose-lp h2 { font-size: 1.4rem; font-weight: 700; margin: 1.2rem 0 .5rem; }
        .prose-lp h3 { font-size: 1.15rem; font-weight: 700; margin: 1rem 0 .4rem; }
        .prose-lp p { margin: .6rem 0; line-height: 1.8; }
        .prose-lp ul { list-style: disc; padding-left: 1.4rem; margin: .6rem 0; }
        .prose-lp ol { list-style: decimal; padding-left: 1.4rem; margin: .6rem 0; }
        .prose-lp img { max-width: 100%; height: auto; border-radius: .75rem; margin: .8rem auto; }
        .prose-lp a { color: var(--brand); text-decoration: underline; }
    </style>
    @include('partials.facebook-pixel')
</head>
<body class="pb-20 md:pb-0">

    {{-- হেডার: শুধু ব্র্যান্ড (ল্যান্ডিং পেজে মেনু থাকে না, যাতে কাস্টমার মনোযোগ না হারায়) --}}
    <header class="bg-white border-b">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg">
                @if($logo)<img src="{{ asset('storage/' . $logo) }}" alt="{{ $siteName }}" class="h-9 w-auto">@else<span>{{ $siteName }}</span>@endif
            </a>
            @if($hotline)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="text-sm font-semibold brand-text">📞 {{ $hotline }}</a>
            @endif
        </div>
    </header>

    {{-- Hero --}}
    <section class="max-w-5xl mx-auto px-4 py-8 md:py-12 grid md:grid-cols-2 gap-8 items-center">
        <div class="order-2 md:order-1 text-center md:text-left">
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">{{ $title }}</h1>
            @if($page->subheadline)<p class="mt-4 text-gray-600 text-lg leading-relaxed">{{ $page->subheadline }}</p>@endif
            <div class="mt-5 flex items-end justify-center md:justify-start gap-3">
                <span class="text-4xl font-extrabold brand-text">৳ {{ number_format($product->price, 0) }}</span>
                @if($product->compare_price && $product->compare_price > $product->price)
                    <span class="text-xl text-gray-400 line-through">৳ {{ number_format($product->compare_price, 0) }}</span>
                    @if($product->discount_percent)<span class="text-sm font-bold text-white bg-red-500 rounded-full px-2 py-0.5 mb-1">-{{ $product->discount_percent }}%</span>@endif
                @endif
            </div>

            @if($page->countdown_ends_at && $page->countdown_ends_at->isFuture())
                <div class="mt-5 inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-2" data-countdown="{{ $page->countdown_ends_at->toIso8601String() }}">
                    <span class="text-sm font-semibold">⏰ অফার শেষ হতে বাকি:</span>
                    <span class="font-bold tabular-nums" data-cd-text>--:--:--</span>
                </div>
            @endif

            <div class="mt-6">
                <a href="#order" class="inline-block brand-bg text-white font-bold text-lg rounded-full px-10 py-3.5 shadow-lg hover:opacity-90">{{ $btn }}</a>
            </div>
        </div>
        <div class="order-1 md:order-2">
            @if($heroUrl)<img src="{{ $heroUrl }}" alt="{{ $product->name }}" class="w-full rounded-2xl shadow-lg object-cover max-h-[28rem]">@endif
        </div>
    </section>

    @if($videoEmbed)
        <section class="max-w-4xl mx-auto px-4 pb-8">
            <div class="aspect-video rounded-2xl overflow-hidden bg-black shadow">
                <iframe src="{{ $videoEmbed }}" class="w-full h-full" loading="lazy" title="ভিডিও" allowfullscreen
                        allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </section>
    @endif

    @if(!empty($page->features))
        <section class="max-w-5xl mx-auto px-4 pb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($page->features as $f)
                    <div class="bg-white rounded-2xl border p-5 text-center shadow-sm">
                        <div class="w-10 h-10 mx-auto rounded-full brand-bg text-white flex items-center justify-center font-bold mb-3">✓</div>
                        <div class="font-bold">{{ $f['title'] ?? '' }}</div>
                        @if(!empty($f['text']))<div class="text-sm text-gray-600 mt-1">{{ $f['text'] }}</div>@endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($page->gallery))
        <section class="max-w-5xl mx-auto px-4 pb-8">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($page->gallery as $img)
                    <img src="{{ asset('storage/' . $img) }}" alt="" loading="lazy" class="w-full rounded-xl object-cover aspect-square">
                @endforeach
            </div>
        </section>
    @endif

    @if(filled($bodyHtml))
        <section class="max-w-3xl mx-auto px-4 pb-8">
            <div class="bg-white rounded-2xl border p-5 md:p-8 prose-lp">{!! $bodyHtml !!}</div>
        </section>
    @endif

    {{-- অর্ডার ফর্ম --}}
    <section id="order" class="max-w-2xl mx-auto px-4 pb-12 scroll-mt-4">
        <div class="bg-white rounded-2xl border-2 brand-border shadow-lg p-5 md:p-8">
            <h2 class="text-xl md:text-2xl font-extrabold text-center mb-5">{{ $page->form_title ?: 'অর্ডার করতে নিচের ফর্মটি পূরণ করুন' }}</h2>

            @if($errors->any() || session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm p-3 space-y-1">
                    @if(session('error'))<div>{{ session('error') }}</div>@endif
                    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('landing.order', $page->slug) }}" id="lp-form" class="space-y-4">
                @csrf
                <div class="flex items-center gap-3 border rounded-xl p-3 bg-gray-50">
                    @if($product->image_url)<img src="{{ $product->image_url }}" alt="" class="w-16 h-16 rounded-lg object-cover">@endif
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold truncate">{{ $product->name }}</div>
                        <div class="brand-text font-bold">৳ {{ number_format($product->price, 0) }}</div>
                    </div>
                    <div class="flex items-center border rounded-lg bg-white">
                        <button type="button" class="px-3 py-1.5 text-lg" data-qty="-1" aria-label="কমান">−</button>
                        <input type="number" name="quantity" id="lp-qty" min="1" max="20" value="{{ old('quantity', 1) }}" class="w-10 text-center outline-none bg-transparent">
                        <button type="button" class="px-3 py-1.5 text-lg" data-qty="1" aria-label="বাড়ান">+</button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">আপনার নাম <span class="text-red-500">*</span></label>
                    <input name="customer_name" value="{{ old('customer_name') }}" required class="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2" placeholder="পুরো নাম">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">মোবাইল নম্বর <span class="text-red-500">*</span></label>
                    <input name="customer_phone" value="{{ old('customer_phone') }}" required inputmode="tel" class="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2" placeholder="01XXXXXXXXX">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">সম্পূর্ণ ঠিকানা <span class="text-red-500">*</span></label>
                    <textarea name="customer_address" rows="2" required class="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2" placeholder="গ্রাম/এলাকা, থানা, জেলা">{{ old('customer_address') }}</textarea>
                </div>

                <div>
                    <div class="text-sm font-semibold mb-2">ডেলিভারি এলাকা</div>
                    @php $zone = old('delivery_zone', 'outside_dhaka'); @endphp
                    <div class="grid gap-2">
                        @foreach(['inside_dhaka' => 'ঢাকা শহরের ভেতরে', 'near_dhaka' => 'ঢাকার আশেপাশে', 'outside_dhaka' => 'ঢাকার বাইরে'] as $k => $label)
                            <label class="flex items-center justify-between border rounded-xl px-4 py-3 cursor-pointer">
                                <span class="flex items-center gap-2"><input type="radio" name="delivery_zone" value="{{ $k }}" @checked($zone === $k) class="accent-green-600"> {{ $label }}</span>
                                <span class="text-sm text-gray-600">৳ {{ \App\Models\Order::DELIVERY_CHARGES[$k] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                @if(count($paymentMethods) > 1)
                    <div>
                        <div class="text-sm font-semibold mb-2">পেমেন্ট মেথড</div>
                        <div class="grid gap-2">
                            @foreach($paymentMethods as $key => $method)
                                <label class="flex items-center gap-2 border rounded-xl px-4 py-3 cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $key }}" @checked($selectedPayment === $key)
                                           @if($method['number']) data-number="{{ $method['number'] }}" data-type="{{ $method['type'] }}" @endif class="accent-green-600">
                                    {{ $method['label'] }}
                                </label>
                            @endforeach
                        </div>
                        <div id="pay-box" class="hidden mt-3 border border-yellow-300 rounded-xl p-4 bg-yellow-50 text-sm space-y-3">
                            <p>নিচের নম্বরে <strong id="pay-type"></strong> করুন:</p>
                            <p class="text-xl font-bold select-all" id="pay-number"></p>
                            <input name="payment_sender_number" value="{{ old('payment_sender_number') }}" class="w-full border rounded-xl px-4 py-2.5" placeholder="যে নম্বর থেকে টাকা পাঠিয়েছেন">
                            <input name="payment_trx_id" value="{{ old('payment_trx_id') }}" class="w-full border rounded-xl px-4 py-2.5 uppercase" placeholder="Transaction ID (TrxID)">
                        </div>
                    </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ array_key_first($paymentMethods) }}">
                @endif

                <div>
                    <label class="block text-sm font-semibold mb-1">নোট (ঐচ্ছিক)</label>
                    <input name="customer_note" value="{{ old('customer_note') }}" class="w-full border rounded-xl px-4 py-3" placeholder="কোনো বিশেষ নির্দেশনা থাকলে লিখুন">
                </div>

                <div class="rounded-xl bg-gray-50 border p-4 text-sm space-y-1">
                    <div class="flex justify-between"><span>সাবটোটাল</span><span>৳ <span id="s-sub">0</span></span></div>
                    <div class="flex justify-between"><span>ডেলিভারি চার্জ</span><span>৳ <span id="s-del">0</span></span></div>
                    <div class="flex justify-between font-extrabold text-lg pt-2 border-t"><span>সর্বমোট</span><span class="brand-text">৳ <span id="s-total">0</span></span></div>
                </div>

                <button type="submit" id="lp-submit" class="w-full brand-bg text-white font-bold text-lg rounded-full py-4 shadow-lg hover:opacity-90">{{ $btn }}</button>
                <p class="text-xs text-center text-gray-500">ক্যাশ অন ডেলিভারিতে পণ্য হাতে পেয়ে টাকা দিন।</p>
            </form>
        </div>
    </section>

    <footer class="text-center text-xs text-gray-500 pb-8">© {{ date('Y') }} {{ $siteName }}</footer>

    {{-- মোবাইলে নিচে স্টিকি বাটন --}}
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t p-3 shadow-[0_-4px_12px_rgba(0,0,0,.08)]">
        <a href="#order" class="block text-center brand-bg text-white font-bold rounded-full py-3">{{ $btn }} — ৳ {{ number_format($product->price, 0) }}</a>
    </div>

    <script>
        (function () {
            const price = {{ (float) $product->price }};
            const charges = { inside_dhaka: 80, near_dhaka: 100, outside_dhaka: 150 };
            const qty = document.getElementById('lp-qty');
            const fmt = n => Math.round(n).toLocaleString('en-US');
            function calc() {
                const q = Math.max(1, Math.min(20, parseInt(qty.value || '1', 10) || 1));
                const z = document.querySelector('input[name="delivery_zone"]:checked');
                const del = charges[z ? z.value : 'outside_dhaka'] || 0;
                document.getElementById('s-sub').textContent = fmt(price * q);
                document.getElementById('s-del').textContent = fmt(del);
                document.getElementById('s-total').textContent = fmt(price * q + del);
            }
            document.querySelectorAll('[data-qty]').forEach(b => b.addEventListener('click', () => {
                qty.value = Math.max(1, Math.min(20, (parseInt(qty.value || '1', 10) || 1) + parseInt(b.dataset.qty, 10)));
                calc();
            }));
            qty.addEventListener('input', calc);
            document.querySelectorAll('input[name="delivery_zone"]').forEach(r => r.addEventListener('change', calc));

            const box = document.getElementById('pay-box');
            function pay() {
                if (!box) return;
                const sel = document.querySelector('input[name="payment_method"]:checked');
                const mobile = !!(sel && sel.dataset.number);
                box.classList.toggle('hidden', !mobile);
                if (mobile) {
                    document.getElementById('pay-number').textContent = sel.dataset.number;
                    document.getElementById('pay-type').textContent = sel.dataset.type || 'Send Money';
                }
            }
            document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', pay));
            calc(); pay();

            // ডাবল সাবমিট ঠেকানো
            document.getElementById('lp-form').addEventListener('submit', function () {
                const b = document.getElementById('lp-submit');
                setTimeout(() => { b.disabled = true; b.style.opacity = .6; }, 0);
            });

            // কাউন্টডাউন
            document.querySelectorAll('[data-countdown]').forEach(el => {
                const end = new Date(el.dataset.countdown).getTime();
                const out = el.querySelector('[data-cd-text]');
                const pad = n => String(n).padStart(2, '0');
                function tick() {
                    const s = Math.max(0, Math.floor((end - Date.now()) / 1000));
                    const d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
                    out.textContent = (d ? d + ' দিন ' : '') + pad(h) + ':' + pad(m) + ':' + pad(sec);
                    if (s <= 0) el.style.display = 'none';
                }
                tick(); setInterval(tick, 1000);
            });
        })();
    </script>
</body>
</html>
