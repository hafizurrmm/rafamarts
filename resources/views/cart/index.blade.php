@extends('layouts.app')

@section('title', 'My Cart')

@section('content')
    <h1 class="text-2xl font-bold mb-6">আপনার কার্ট</h1>

    @if(empty($cart))
        <p class="text-gray-500">আপনার কার্টে কোনো প্রোডাক্ট নেই। <a href="{{ route('home') }}" class="text-green-600 underline">শপিং করুন</a></p>
    @else
        <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-3">Product</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Quantity</th>
                        <th class="p-3">Total</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @php $subTotal = 0; @endphp
                    @foreach($cart as $item)
                        @php $subTotal += $item['price'] * $item['quantity']; @endphp
                        <tr class="border-t">
                            <td class="p-3 flex items-center gap-3">
                                <img src="{{ $item['image'] }}" class="w-12 h-12 object-cover rounded">
                                {{ $item['name'] }}
                            </td>
                            <td class="p-3">৳ {{ number_format($item['price'], 0) }}</td>
                            <td class="p-3">
                                <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="flex items-center gap-1">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1"
                                           class="w-16 border rounded px-2 py-1" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td class="p-3 font-semibold">৳ {{ number_format($item['price'] * $item['quantity'], 0) }}</td>
                            <td class="p-3">
                                <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow-sm p-4 max-w-sm ml-auto">
            <div class="flex justify-between mb-2">
                <span>Sub-Total</span>
                <span>৳ {{ number_format($subTotal, 0) }}</span>
            </div>
            <p class="text-gray-500 text-xs mb-4">ডেলিভারি চার্জ চেকআউট পেজে যোগ হবে।</p>
            <a href="{{ route('checkout.index') }}"
               class="block text-center bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded">
                অর্ডার কনফার্ম করুন
            </a>
        </div>
    @endif
@endsection
