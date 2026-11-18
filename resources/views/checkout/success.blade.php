@extends('layouts.app')

@section('title', 'Order Successful')

@section('content')
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="text-5xl mb-4">✅</div>
        <h1 class="text-2xl font-bold mb-2">অর্ডার সফল হয়েছে!</h1>
        <p class="text-gray-600 mb-4">আপনার অর্ডার নম্বর: <span class="font-bold">{{ $order->order_number }}</span></p>
        <p class="text-gray-600 mb-6">
            @if($order->isMobilePayment())
                আমরা আপনার পেমেন্ট যাচাই করে শীঘ্রই অর্ডার কনফার্ম করব।
            @else
                আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।
            @endif
        </p>

        <div class="text-left bg-gray-50 rounded p-4 mb-6 text-sm">
            <div class="flex justify-between mb-1"><span>নাম</span><span>{{ $order->customer_name }}</span></div>
            <div class="flex justify-between mb-1"><span>ফোন</span><span>{{ $order->customer_phone }}</span></div>
            <div class="flex justify-between mb-1"><span>ঠিকানা</span><span>{{ $order->customer_address }}</span></div>
            @if($order->isMobilePayment())
                <div class="flex justify-between mb-1"><span>পেমেন্ট</span><span>{{ $order->paymentLabel() }} — TrxID: {{ $order->payment_trx_id }}</span></div>
            @endif
            <div class="flex justify-between mb-1"><span>Sub-Total</span><span>৳ {{ number_format($order->sub_total, 0) }}</span></div>
            <div class="flex justify-between mb-1"><span>Delivery</span><span>৳ {{ number_format($order->delivery_charge, 0) }}</span></div>
            <div class="flex justify-between font-bold border-t pt-2"><span>Total</span><span>৳ {{ number_format($order->total_amount, 0) }}</span></div>
        </div>

        <a href="{{ route('home') }}" class="inline-block bg-green-600 text-white font-semibold px-6 py-2 rounded">
            আরও কেনাকাটা করুন
        </a>
    </div>
@endsection
