@php
    $img = \App\Support\PageBlocks::img($data['image'] ?? null);
    $url = \App\Support\PageBlocks::safeUrl($data['button_url'] ?? null);
    $right = ($data['image_position'] ?? 'left') === 'right';
@endphp
@if($img)
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden grid md:grid-cols-2 items-center">
        <img src="{{ $img }}" alt="{{ $data['heading'] ?? '' }}" class="w-full h-full object-cover max-h-[28rem] {{ $right ? 'md:order-2' : '' }}" loading="lazy" decoding="async">
        <div class="p-6 md:p-10">
            @if(!empty($data['heading']))
                <h2 class="text-2xl md:text-3xl font-bold mb-3">{{ $data['heading'] }}</h2>
            @endif
            @if(!empty($data['text']))
                <div class="rich-content text-gray-700">{!! $data['text'] !!}</div>
            @endif
            @if(!empty($data['button_text']) && $url)
                <a href="{{ $url }}" class="mt-4 inline-block brand-bg text-white font-bold px-7 py-3 rounded-full hover:opacity-90">{{ $data['button_text'] }}</a>
            @endif
        </div>
    </section>
@endif
