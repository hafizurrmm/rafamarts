@extends('layouts.app')

@section('title', 'নতুন একাউন্ট')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl md:text-3xl font-bold text-center mb-6">নতুন একাউন্ট খুলুন</h1>

    <form method="POST" action="{{ route('register.attempt') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">আপনার নাম <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-semibold mb-1">মোবাইল নম্বর <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="উদাহরণ: 017XXXXXXXX"
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
            @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-semibold mb-1">পাসওয়ার্ড (কমপক্ষে ৬ অক্ষর) <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="6"
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block font-semibold mb-1">পাসওয়ার্ড আবার লিখুন <span class="text-red-500">*</span></label>
            <input type="password" name="password_confirmation" required minlength="6"
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
        </div>
        <button type="submit" class="w-full py-3 rounded-lg font-bold text-white brand-bg hover:opacity-90">একাউন্ট খুলুন</button>
        <p class="text-center text-sm text-gray-600">আগেই একাউন্ট আছে? <a href="{{ route('login') }}" class="brand-text font-semibold">সাইন ইন করুন</a></p>
    </form>
</div>
@endsection
