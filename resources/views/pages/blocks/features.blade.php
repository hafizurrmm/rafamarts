@php $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => !empty($i['title']))); @endphp
@if($items)
    <section>
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            @foreach($items as $item)
                <div class="bg-white rounded-2xl border border-gray-100 p-4 md:p-5 text-center">
                    <div class="text-3xl md:text-4xl mb-2">{{ $item['icon'] ?? '✅' }}</div>
                    <div class="font-bold text-gray-800">{{ $item['title'] }}</div>
                    @if(!empty($item['text']))
                        <div class="text-sm text-gray-500 mt-1">{{ $item['text'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
