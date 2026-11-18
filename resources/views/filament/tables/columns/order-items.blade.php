@php($record = $getRecord())
@php($items = $record->items)
<div class="px-3 py-2 text-sm min-w-[12rem]">
    @foreach($items->take(2) as $item)
        <div class="flex items-center gap-2 mb-1">
            <img src="{{ $item->product?->image_url ?? asset('images/placeholder-product.png') }}" alt=""
                 class="w-10 h-10 rounded object-cover flex-shrink-0">
            <div class="whitespace-normal leading-snug">
                <span class="font-semibold">{{ $item->quantity }}×</span> {{ \Illuminate\Support\Str::limit($item->product_name, 40) }}
            </div>
        </div>
    @endforeach
    @if($items->count() > 2)
        <div class="text-xs text-gray-500">+ আরও {{ $items->count() - 2 }}টা প্রোডাক্ট</div>
    @endif
</div>
