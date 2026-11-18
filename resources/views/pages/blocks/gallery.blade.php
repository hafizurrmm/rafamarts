@php
    $images = array_values(array_filter((array) ($data['images'] ?? [])));
    $cols = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-3', '4' => 'md:grid-cols-4'][(string) ($data['columns'] ?? '3')] ?? 'md:grid-cols-3';
@endphp
@if($images)
    <section>
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid grid-cols-2 {{ $cols }} gap-2 md:gap-4">
            @foreach($images as $path)
                <img src="{{ \App\Support\PageBlocks::img($path) }}" alt="" class="w-full aspect-square object-cover rounded-xl" loading="lazy" decoding="async">
            @endforeach
        </div>
    </section>
@endif
