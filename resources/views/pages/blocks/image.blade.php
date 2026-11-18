@php
    $img = \App\Support\PageBlocks::img($data['image'] ?? null);
    $url = \App\Support\PageBlocks::safeUrl($data['link_url'] ?? null);
    $width = ['full' => 'w-full', 'wide' => 'w-full max-w-5xl mx-auto', 'narrow' => 'w-full max-w-2xl mx-auto'][$data['width'] ?? 'full'] ?? 'w-full';
@endphp
@if($img)
    <figure class="{{ $width }}">
        @if($url)<a href="{{ $url }}">@endif
            <img src="{{ $img }}" alt="{{ $data['alt'] ?? '' }}" class="w-full h-auto rounded-2xl" loading="lazy" decoding="async">
        @if($url)</a>@endif
    </figure>
@endif
