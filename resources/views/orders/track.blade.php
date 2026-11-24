@extends('layouts.app')

@section('title', 'Order Tracking')

@php
    $statusLabels = [
        'pending' => ['অর্ডার গৃহীত — কনফার্মেশনের অপেক্ষায়', 'bg-yellow-100 text-yellow-800'],
        'processing' => ['প্রসেসিং চলছে', 'bg-blue-100 text-blue-800'],
        'on_hold' => ['অর্ডার হোল্ডে আছে', 'bg-orange-100 text-orange-800'],
        'completed' => ['ডেলিভারি সম্পন্ন', 'bg-green-100 text-green-800'],
        'cancelled' => ['অর্ডার বাতিল হয়েছে', 'bg-red-100 text-red-800'],
        'returned' => ['অর্ডার ফেরত এসেছে', 'bg-purple-100 text-purple-800'],
    ];
@endphp

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-3xl font-bold text-center mb-8">Order Tracking</h1>

    <div class="text-center mb-6">
        <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center text-4xl mx-auto mb-4">📦</div>
        <h2 class="text-xl font-bold mb-2">আপনার অর্ডারের বর্তমান অবস্থা ট্র্যাক করুন</h2>
        <p class="text-gray-600 text-sm">অর্ডার কনফার্মেশনের সময় দেওয়া <strong>অর্ডার নম্বর (Order ID)</strong> এবং <strong>ফোন নম্বর</strong> লিখুন</p>
    </div>

    <form method="POST" action="{{ route('track.lookup') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
        @csrf
        <div>
            <label class="block font-semibold mb-1">অর্ডার আইডি (Order ID) <span class="text-red-500">*</span></label>
            <div class="flex items-center border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-[var(--brand-primary)]">
                <span class="px-3 text-gray-400 font-bold">#</span>
                <input type="text" name="order_id" required value="{{ old('order_id') }}" placeholder="উদাহরণ: ORD-AB12CD34"
                       class="w-full py-3 pr-3 outline-none">
            </div>
        </div>
        <div>
            <label class="block font-semibold mb-1">মোবাইল নম্বর (Phone Number) <span class="text-red-500">*</span></label>
            <div class="flex items-center border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-[var(--brand-primary)]">
                <span class="px-3 text-gray-400">📞</span>
                <input type="tel" name="phone" required value="{{ old('phone') }}" placeholder="উদাহরণ: 017XXXXXXXX"
                       class="w-full py-3 pr-3 outline-none">
            </div>
        </div>
        @if($errors->any())
            <p class="text-red-600 text-sm">অর্ডার আইডি ও ফোন নম্বর দুটোই সঠিকভাবে লিখুন।</p>
        @endif
        <button type="submit" class="w-full py-3 rounded-lg font-bold text-white shadow brand-bg hover:opacity-90">
            🔍 অর্ডার ট্র্যাক করুন
        </button>
    </form>

    @if($searched && ! $order && ! $errors->any())
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-center">
            অর্ডার পাওয়া যায়নি। অর্ডার আইডি ও ফোন নম্বর মিলিয়ে আবার চেষ্টা করুন।
        </div>
    @endif

    @if($order)
        @php [$label, $badge] = $statusLabels[$order->status] ?? [ucfirst($order->status), 'bg-gray-100 text-gray-800']; @endphp
        <div class="mt-6 bg-white rounded-xl shadow-sm border p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <div>
                    <div class="text-sm text-gray-500">অর্ডার নম্বর</div>
                    <div class="font-bold text-lg">{{ $order->order_number }}</div>
                    <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badge }}">{{ $label }}</span>
            </div>

            @if(in_array($order->status, ['cancelled', 'returned'], true))
                <p class="text-sm text-red-600 mb-4">{{ $order->status === 'returned' ? 'এই অর্ডারটি ফেরত এসেছে।' : 'এই অর্ডারটি বাতিল করা হয়েছে।' }} প্রয়োজনে আমাদের সাথে যোগাযোগ করুন।</p>
            @else
                <ol class="mb-5">
                    @foreach($steps as $i => $s)
                        <li class="flex items-start gap-3">
                            <div class="flex flex-col items-center">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $s['done'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">{{ $s['done'] ? '✓' : $i + 1 }}</span>
                                @if(! $loop->last)<span class="w-0.5 h-6 {{ $steps[$i + 1]['done'] ? 'bg-green-500' : 'bg-gray-200' }}"></span>@endif
                            </div>
                            <span class="{{ $s['done'] ? 'font-semibold' : 'text-gray-500' }}">{{ $s['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if($order->courier_name || $order->tracking_code || $order->consignment_id)
                <div class="bg-gray-50 rounded p-3 text-sm mb-4 space-y-1">
                    @if($order->courier_name)<div class="flex justify-between"><span>কুরিয়ার</span><span class="font-semibold">{{ $order->courier_name }}</span></div>@endif
                    @if($order->tracking_code)<div class="flex justify-between"><span>ট্র্যাকিং কোড</span><span class="font-semibold">{{ $order->tracking_code }}</span></div>@endif
                    @if($order->consignment_id)<div class="flex justify-between"><span>কনসাইনমেন্ট ID</span><span class="font-semibold">{{ $order->consignment_id }}</span></div>@endif
                </div>
            @endif

            <div class="text-sm border-t pt-3">
                @foreach($order->items as $item)
                    <div class="flex justify-between py-1"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><span>৳ {{ number_format($item->line_total, 0) }}</span></div>
                @endforeach
                <div class="flex justify-between py-1 text-gray-600"><span>ডেলিভারি চার্জ</span><span>৳ {{ number_format($order->delivery_charge, 0) }}</span></div>
                @if((float) $order->discount > 0)
                    <div class="flex justify-between py-1 text-gray-600"><span>ডিসকাউন্ট</span><span>− ৳ {{ number_format($order->discount, 0) }}</span></div>
                @endif
                <div class="flex justify-between py-2 border-t font-bold"><span>সর্বমোট</span><span>৳ {{ number_format($order->total_amount, 0) }}</span></div>
                <div class="flex justify-between text-gray-600">
                    <span>পেমেন্ট</span>
                    <span>{{ $order->paymentLabel() }}@if($order->isMobilePayment()) — {{ $order->payment_verified ? 'যাচাই হয়েছে ✓' : 'যাচাইয়ের অপেক্ষায়' }}@endif</span>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
