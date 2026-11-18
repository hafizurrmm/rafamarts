@php
    $url = \App\Support\PageBlocks::safeUrl($data['button_url'] ?? null);
    $style = $data['style'] ?? 'brand';
    $wrap = ['brand' => 'brand-bg text-white', 'dark' => 'brand-bg-secondary text-white', 'light' => 'bg-white text-gray-800 border border-gray-200'][$style] ?? 'brand-bg text-white';
    $btn = $style === 'light' ? 'brand-bg text-white' : 'bg-white text-gray-900';
@endphp
<section class="rounded-2xl p-8 md:p-12 text-center {{ $wrap }}">
    @if(!empty($data['heading']))
        <h2 class="text-2xl md:text-4xl font-extrabold">{{ $data['heading'] }}</h2>
    @endif
    @if(!empty($data['text']))
        <p class="mt-3 text-base md:text-lg opacity-90 max-w-2xl mx-auto">{{ $data['text'] }}</p>
    @endif
    @if(!empty($data['button_text']) && $url)
        <a href="{{ $url }}" class="mt-5 inline-block {{ $btn }} font-bold px-8 py-3 rounded-full hover:opacity-90 shadow">{{ $data['button_text'] }}</a>
    @endif
</section>
