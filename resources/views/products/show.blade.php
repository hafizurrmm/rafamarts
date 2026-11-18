@extends('layouts.app')

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags($product->description ?? $product->name), 160))
@section('meta_image', $product->image_url)

@section('content')
    <div class="grid md:grid-cols-2 gap-8 bg-white p-6 rounded-lg shadow-sm">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full rounded-lg object-cover" fetchpriority="high">

        <div>
            <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>

            <div class="mb-4">
                <span class="text-2xl text-red-600 font-bold">৳ {{ number_format($product->price, 0) }}</span>
                @if($product->compare_price)
                    <span class="text-gray-400 line-through ml-2">৳ {{ number_format($product->compare_price, 0) }}</span>
                @endif
            </div>

            <p class="text-gray-600 mb-6">{{ $product->description }}</p>

            <form action="{{ route('cart.add', $product) }}" method="POST" class="flex items-center gap-3">
                @csrf
                <input type="number" name="quantity" value="1" min="1" class="w-20 border rounded px-2 py-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded">
                    🛍️ কার্টে যোগ করুন
                </button>
            </form>
        </div>
    </div>

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
