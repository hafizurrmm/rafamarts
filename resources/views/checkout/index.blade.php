@extends('layouts.app')

@section('title', 'Checkout')

@section('content')

    @php
        $subTotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
    @endphp

    <div class="grid md:grid-cols-2 gap-8">

        {{-- Left: customer form (matches screenshot 1) --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <p class="mb-4 text-sm">
                অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নাম্বার, লিখে
                <span class="text-red-600 font-semibold">অর্ডার কনফার্ম করুন</span> বাটনে ক্লিক করুন
            </p>

            @if($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('checkout.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">আপনার নাম <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                           placeholder="আপনার নাম"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">আপনার মোবাইল নম্বর <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                           placeholder="আপনার মোবাইল নম্বর"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">আপনার সম্পূর্ণ ঠিকানা (জেলা ও থানা সহ)</label>
                    <input type="text" name="customer_address" value="{{ old('customer_address') }}" required
                           placeholder="আপনার সম্পূর্ণ ঠিকানা"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">আপনার মন্তব্য (ঐচ্ছিক)</label>
                    <input type="text" name="customer_note" value="{{ old('customer_note') }}"
                           placeholder="কালার, সাইজ, অর্ডার অথবা ডেলিভারি সম্পর্কে যে কোন কথা যদি থাকে!"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">কুরিয়ার চার্জ</label>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between px-4 py-3 rounded cursor-pointer border
                                      has-[:checked]:bg-green-600 has-[:checked]:text-white">
                            <span>ঢাকার বাহিরে ১৫০ টাকা</span>
                            <input type="radio" name="delivery_zone" value="outside_dhaka" checked class="accent-green-600">
                        </label>
                        <label class="flex items-center justify-between px-4 py-3 rounded cursor-pointer border bg-gray-100
                                      has-[:checked]:bg-green-600 has-[:checked]:text-white">
                            <span>ঢাকার পার্শ্ববর্তী ১০০ টাকা</span>
                            <input type="radio" name="delivery_zone" value="near_dhaka" class="accent-green-600">
                        </label>
                        <label class="flex items-center justify-between px-4 py-3 rounded cursor-pointer border bg-gray-100
                                      has-[:checked]:bg-green-600 has-[:checked]:text-white">
                            <span>ঢাকার ভিতরে ৮০ টাকা</span>
                            <input type="radio" name="delivery_zone" value="inside_dhaka" class="accent-green-600">
                        </label>
                    </div>
                </div>

                @php $selectedPayment = old('payment_method', array_key_first($paymentMethods)); @endphp

                @if(count($paymentMethods) > 1)
                    <div>
                        <label class="block text-sm font-medium mb-2">পেমেন্ট মেথড</label>
                        <div class="space-y-2">
                            @foreach($paymentMethods as $key => $method)
                                <label class="flex items-center justify-between px-4 py-3 rounded cursor-pointer border bg-gray-100
                                              has-[:checked]:bg-green-600 has-[:checked]:text-white">
                                    <span>{{ $method['label'] }}</span>
                                    <input type="radio" name="payment_method" value="{{ $key }}"
                                           data-label="{{ $method['label'] }}"
                                           @if($method['number']) data-number="{{ $method['number'] }}" data-type="{{ $method['type'] }}" @endif
                                           @checked($selectedPayment === $key) class="accent-green-600">
                                </label>
                            @endforeach
                        </div>

                        {{-- bKash/Nagad/Rocket বাছলে নম্বর ও TrxID এর ঘর দেখায় (JS নিচে) --}}
                        <div id="payment-box" class="hidden mt-3 border border-yellow-300 rounded p-4 bg-yellow-50 text-sm space-y-3">
                            <p>
                                আপনার <span class="font-bold" id="pay-label"></span> থেকে
                                <span class="font-bold" id="pay-type"></span> করে
                                <span class="font-bold text-red-600">৳ <span id="pay-amount"></span></span> টাকা এই নম্বরে পাঠান:
                            </p>
                            <p class="text-xl font-bold select-all" id="pay-number"></p>

                            <div>
                                <label class="block text-sm font-medium mb-1">যে নম্বর থেকে টাকা পাঠিয়েছেন <span class="text-red-500">*</span></label>
                                <input type="text" name="payment_sender_number" value="{{ old('payment_sender_number') }}"
                                       placeholder="01XXXXXXXXX"
                                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">TrxID (ট্রানজেকশন আইডি) <span class="text-red-500">*</span></label>
                                <input type="text" name="payment_trx_id" value="{{ old('payment_trx_id') }}"
                                       placeholder="যেমন: 8N7A6D5EE7" autocomplete="off"
                                       class="w-full border rounded px-3 py-2 uppercase focus:outline-none focus:ring-2 focus:ring-green-500">
                                <p class="text-xs text-gray-500 mt-1">টাকা পাঠানোর পর SMS/অ্যাপে TrxID পাবেন। ঠিক না হলে অর্ডার কনফার্ম হবে না।</p>
                            </div>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ array_key_first($paymentMethods) }}">
                @endif

                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded">
                    অর্ডার কনফার্ম করুন
                </button>
            </form>
        </div>

        {{-- Right: order summary (matches screenshot 1) --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <table class="w-full text-sm mb-4">
                <thead class="text-left border-b">
                    <tr>
                        <th class="py-2">Product</th>
                        <th class="py-2">Price</th>
                        <th class="py-2">Quantity</th>
                        <th class="py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $item)
                        <tr class="border-b">
                            <td class="py-3 flex items-center gap-2">
                                <img src="{{ $item['image'] }}" class="w-10 h-10 object-cover rounded">
                                {{ $item['name'] }}
                            </td>
                            <td class="py-3">৳ {{ number_format($item['price'], 2) }}</td>
                            <td class="py-3">{{ $item['quantity'] }}</td>
                            <td class="py-3">৳ {{ number_format($item['price'] * $item['quantity'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span>Sub-Total</span>
                    <span>৳ {{ number_format($subTotal, 0) }} টাকা</span>
                </div>
                <div class="flex justify-between">
                    <span>Delivery Charges</span>
                    <span id="delivery-charge-label">১৫০ টাকা</span>
                </div>
                <div class="flex justify-between text-red-600 font-bold text-base border-t pt-2">
                    <span>Total Amount</span>
                    <span>৳ {{ number_format($subTotal + 150, 0) }} টাকা</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const subTotal = {{ $subTotal }};
        const charges = { outside_dhaka: 150, near_dhaka: 100, inside_dhaka: 80 };
        const labels = { outside_dhaka: '১৫০ টাকা', near_dhaka: '১০০ টাকা', inside_dhaka: '৮০ টাকা' };

        document.querySelectorAll('input[name="delivery_zone"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const charge = charges[this.value];
                document.getElementById('delivery-charge-label').innerText = labels[this.value];
                document.querySelector('.text-red-600.font-bold.text-base span:last-child').innerText =
                    '৳ ' + (subTotal + charge).toLocaleString() + ' টাকা';
            });
        });

        // --- bKash / Nagad / Rocket ম্যানুয়াল পেমেন্ট বক্স ---
        const payBox = document.getElementById('payment-box');

        function syncPaymentBox() {
            if (!payBox) return;

            const selected = document.querySelector('input[name="payment_method"]:checked');
            const isMobile = !!(selected && selected.dataset.number);

            payBox.classList.toggle('hidden', !isMobile);
            // লুকানো অবস্থায় ঘরগুলো disabled — COD অর্ডারে এগুলো সাবমিট বা required হবে না
            payBox.querySelectorAll('input').forEach(i => { i.disabled = !isMobile; i.required = isMobile; });

            if (!isMobile) return;

            const zone = document.querySelector('input[name="delivery_zone"]:checked');
            document.getElementById('pay-label').innerText = selected.dataset.label;
            document.getElementById('pay-type').innerText = selected.dataset.type;
            document.getElementById('pay-number').innerText = selected.dataset.number;
            document.getElementById('pay-amount').innerText = (subTotal + charges[zone.value]).toLocaleString();
        }

        document.querySelectorAll('input[name="payment_method"], input[name="delivery_zone"]')
            .forEach(r => r.addEventListener('change', syncPaymentBox));
        syncPaymentBox();

        // --- অসম্পূর্ণ চেকআউট সেভ (ফোন নম্বর দেওয়ার পর, অর্ডার না করে চলে গেলে ফলো-আপের জন্য) ---
        (function () {
            const form = document.querySelector('form[action="{{ route('checkout.store') }}"]');
            if (!form) return;
            let timer = null, last = '';

            function send() {
                const data = new FormData(form);
                const phone = (data.get('customer_phone') || '').toString().replace(/\D+/g, '');
                if (phone.length < 11) return;

                const body = new URLSearchParams();
                ['_token', 'customer_name', 'customer_phone', 'customer_address', 'delivery_zone'].forEach(k => body.append(k, data.get(k) || ''));
                const key = body.toString();
                if (key === last) return;
                last = key;

                fetch('{{ route('checkout.capture') }}', {
                    method: 'POST', body: body, keepalive: true, credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).catch(() => {});
            }

            ['customer_name', 'customer_phone', 'customer_address'].forEach(n => {
                const el = form.elements[n];
                if (el) el.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(send, 1200); });
            });
            document.querySelectorAll('input[name="delivery_zone"]').forEach(r => r.addEventListener('change', send));
            document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') send(); });
            window.addEventListener('pagehide', send);
        })();
    </script>

    @push('fb_page_events')
        fbq('track', 'InitiateCheckout', {!! json_encode([
            'content_ids' => collect($cart)->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
            'content_type' => 'product',
            'num_items' => collect($cart)->sum('quantity'),
            'value' => (float) $subTotal,
            'currency' => 'BDT',
        ]) !!}, {eventID: '{{ $fbEventId }}'});
    @endpush
@endsection
