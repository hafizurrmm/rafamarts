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
            <div class="bg-white border rounded-lg p-2 shadow-sm">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full rounded-lg object-cover" fetchpriority="high">
            </div>
        </div>

        {{-- ডানে বিবরণ: ৭ কলাম --}}
        <div class="md:col-span-7 bg-white rounded-lg shadow-sm p-5">
            <h1 class="text-2xl font-bold mb-2 leading-snug">{{ $product->name }}</h1>

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
                   class="block w-full text-center text-white font-semibold px-6 py-2.5 rounded hover:opacity-90 transition mb-4"
                   style="background-color:#0d6efd">
                    অর্ডার করতে কল করুন 📞 {{ $hotline }}
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
