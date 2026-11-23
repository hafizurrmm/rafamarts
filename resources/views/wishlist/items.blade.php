@foreach($products as $product)
    @include('home.partials.product-card', ['product' => $product])
@endforeach
