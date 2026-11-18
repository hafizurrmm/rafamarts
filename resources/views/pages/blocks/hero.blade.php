@php
    $img = \App\Support\PageBlocks::img($data['image'] ?? null);
    $url = \App\Support\PageBlocks::safeUrl($data['button_url'] ?? null);
    $align = ['left' => 'items-start text-left', 'center' => 'items-center text-center', 'right' => 'items-end text-right'][$data['align'] ?? 'center'] ?? 'items-center text-center';
    $height = ['sm' => 'min-h-[14rem] md:min-h-[18rem]', 'md' => 'min-h-[18rem] md:min-h-[26rem]', 'lg' => 'min-h-[22rem] md:min-h-[34rem]'][$data['height'] ?? 'md'] ?? 'min-h-[18rem] md:min-h-[26rem]';
    $overlay = max(0, min(80, (int) ($data['overlay'] ?? 40)));
    $first = isset($loop) && $loop->first; // প্রথম ব্লক হলে ছবি আগে লোড হোক (LCP)
@endphp
<section class="relative overflow-hidden rounded-2xl brand-bg flex {{ $height }}">
    @if($img)
        <img src="{{ $img }}" alt="{{ $data['heading'] ?? '' }}" class="absolute inset-0 w-full h-full object-cover"
             @if($first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
        <div class="absolute inset-0" style="background: rgba(0,0,0,{{ $overlay / 100 }})"></div>
    @endif
    <div class="relative z-10 w-full flex flex-col justify-center gap-3 md:gap-4 p-6 md:p-12 text-white {{ $align }}">
        @if(!empty($data['heading']))
            <h2 class="text-3xl md:text-5xl font-extrabold leading-tight max-w-3xl">{{ $data['heading'] }}</h2>
        @endif
        @if(!empty($data['subheading']))
            <p class="text-base md:text-xl max-w-2xl opacity-95">{{ $data['subheading'] }}</p>
        @endif
        @if(!empty($data['button_text']) && $url)
            <a href="{{ $url }}" class="mt-2 inline-block bg-white text-gray-900 font-bold px-7 py-3 rounded-full hover:opacity-90 shadow">{{ $data['button_text'] }}</a>
        @endif
    </div>
</section>
