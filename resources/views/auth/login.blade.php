@extends('layouts.app')

@section('title', 'সাইন ইন')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-2xl md:text-3xl font-bold text-center mb-6">সাইন ইন</h1>

    <form method="POST" action="{{ route('login.attempt') }}" class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
        @csrf
        <div>
            <label class="block font-semibold mb-1">মোবাইল নম্বর <span class="text-red-500">*</span></label>
            <input type="tel" name="phone" value="{{ old('phone') }}" required autofocus placeholder="উদাহরণ: 017XXXXXXXX"
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
        </div>
        <div>
            <label class="block font-semibold mb-1">পাসওয়ার্ড <span class="text-red-500">*</span></label>
            <input type="password" name="password" required
                   class="w-full border rounded-lg px-3 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]">
        </div>
        @error('phone')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="remember" value="1"> আমাকে মনে রাখুন</label>
        <button type="submit" class="w-full py-3 rounded-lg font-bold text-white brand-bg hover:opacity-90">সাইন ইন করুন</button>
        <p class="text-center text-sm text-gray-600">একাউন্ট নেই? <a href="{{ route('register') }}" class="brand-text font-semibold">নতুন একাউন্ট খুলুন</a></p>
    </form>
</div>
@endsection
