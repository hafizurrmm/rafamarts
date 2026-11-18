@php
    $products = \App\Support\PageBlocks::products($data);
    $cols = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-3', '4' => 'md:grid-cols-4', '5' => 'md:grid-cols-5'][(string) ($data['columns'] ?? '4')] ?? 'md:grid-cols-4';
@endphp
@if($products->isNotEmpty())
    <section>
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid grid-cols-2 {{ $cols }} gap-3 md:gap-4">
            @foreach($products as $product)
                @include('home.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
@endif
