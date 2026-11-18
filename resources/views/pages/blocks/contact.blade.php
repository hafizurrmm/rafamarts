@php
    $hotline = ($data['show_call'] ?? true) ? \App\Support\PageBlocks::setting('hotline_number') : null;
    $wa = ($data['show_whatsapp'] ?? true) ? \App\Support\PageBlocks::whatsappLink(\App\Support\PageBlocks::setting('whatsapp_number')) : null;
    $fb = ($data['show_facebook'] ?? true) ? \App\Support\PageBlocks::setting('facebook_url') : null;
    $map = $data['map_embed_url'] ?? null;
    $map = ($map && str_starts_with($map, 'https://www.google.com/maps/embed')) ? $map : null; // শুধু Google Maps এমবেড
@endphp
<section class="bg-white rounded-2xl border border-gray-100 p-5 md:p-8">
    @if(!empty($data['heading']))
        <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
    @endif
    <div class="grid md:grid-cols-2 gap-6">
        <div class="space-y-3 text-gray-700">
            @if(!empty($data['address']))<p>📍 {{ $data['address'] }}</p>@endif
            @if($hotline)<p>📞 <a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="font-semibold hover:underline">{{ $hotline }}</a></p>@endif
            @if(!empty($data['email']))<p>✉️ <a href="mailto:{{ $data['email'] }}" class="hover:underline">{{ $data['email'] }}</a></p>@endif
            @if(!empty($data['hours']))<p>🕒 {{ $data['hours'] }}</p>@endif
            <div class="flex flex-wrap gap-2 pt-2">
                @if($hotline)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $hotline) }}" class="brand-bg text-white font-semibold px-5 py-2 rounded-full hover:opacity-90">📞 কল করুন</a>@endif
                @if($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="bg-green-500 text-white font-semibold px-5 py-2 rounded-full hover:opacity-90">💬 WhatsApp</a>@endif
                @if($fb)<a href="{{ $fb }}" target="_blank" rel="noopener" class="bg-blue-600 text-white font-semibold px-5 py-2 rounded-full hover:opacity-90">📘 Facebook</a>@endif
            </div>
        </div>
        @if($map)
            <iframe src="{{ $map }}" class="w-full h-64 rounded-xl border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="ম্যাপ"></iframe>
        @endif
    </div>
</section>
