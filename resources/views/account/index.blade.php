@extends('layouts.app')

@section('title', 'আমার একাউন্ট')

@php
    $badges = [
        'pending' => ['অর্ডার গৃহীত', 'bg-yellow-100 text-yellow-800'],
        'processing' => ['প্রসেসিং', 'bg-blue-100 text-blue-800'],
        'on_hold' => ['হোল্ডে', 'bg-orange-100 text-orange-800'],
        'completed' => ['ডেলিভারি সম্পন্ন', 'bg-green-100 text-green-800'],
        'cancelled' => ['বাতিল', 'bg-red-100 text-red-800'],
    ];
@endphp

@section('content')
<div class="max-w-4xl mx-auto grid md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <form method="POST" action="{{ route('account.update') }}" class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
            @csrf
            <h2 class="font-bold text-lg">আমার তথ্য</h2>
            @if(session('status'))<p class="text-green-700 text-sm">{{ session('status') }}</p>@endif
            <div>
                <label class="block text-sm font-semibold mb-1">নাম</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border rounded-lg px-3 py-2">
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">মোবাইল</label>
                <input type="text" value="{{ $user->phone }}" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-500">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">ডিফল্ট ঠিকানা <span class="text-gray-400 font-normal">(চেকআউটে অটো বসবে)</span></label>
                <textarea name="address" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('address', $user->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">নতুন পাসওয়ার্ড <span class="text-gray-400 font-normal">(বদলাতে চাইলে)</span></label>
                <input type="password" name="password" minlength="6" class="w-full border rounded-lg px-3 py-2">
                @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">পাসওয়ার্ড আবার</label>
                <input type="password" name="password_confirmation" minlength="6" class="w-full border rounded-lg px-3 py-2">
            </div>
            <button class="w-full py-2.5 rounded-lg font-bold text-white brand-bg hover:opacity-90">সেভ করুন</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="w-full py-2.5 rounded-lg border text-gray-700 hover:bg-gray-50">সাইন আউট</button>
        </form>
    </div>

    <div class="md:col-span-2">
        <h2 class="font-bold text-lg mb-3">আমার অর্ডার</h2>

        @forelse($orders as $order)
            @php [$label, $badge] = $badges[$order->status] ?? [ucfirst($order->status), 'bg-gray-100 text-gray-800']; @endphp
            <div class="bg-white rounded-xl shadow-sm border p-4 mb-3">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div>
                        <div class="font-bold">{{ $order->order_number }}</div>
                        <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                </div>
                <div class="text-sm text-gray-700">
                    @foreach($order->items as $item)
                        <div>{{ $item->product_name }} × {{ $item->quantity }}</div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between mt-2 pt-2 border-t text-sm">
                    <span class="font-bold">মোট ৳ {{ number_format($order->total_amount, 0) }}</span>
                    <a href="{{ route('track.show') }}" class="brand-text font-semibold">📦 ট্র্যাক করুন</a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border p-8 text-center text-gray-600">
                এখনো কোনো অর্ডার নেই।
                <div class="mt-3"><a href="{{ route('home') }}" class="inline-block brand-bg text-white font-semibold px-6 py-2 rounded-full">কেনাকাটা শুরু করুন</a></div>
            </div>
        @endforelse

        {{ $orders->links() }}
    </div>
</div>
@endsection
