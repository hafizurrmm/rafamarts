@php $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => !empty($i['name']) && !empty($i['text']))); @endphp
@if($items)
    <section>
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid md:grid-cols-3 gap-3 md:gap-4">
            @foreach($items as $item)
                @php $rating = max(1, min(5, (int) ($item['rating'] ?? 5))); $photo = \App\Support\PageBlocks::img($item['photo'] ?? null); @endphp
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <div class="text-yellow-400 text-lg mb-2" aria-label="{{ $rating }} স্টার">{{ str_repeat('★', $rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $rating) }}</span></div>
                    <p class="text-gray-700 mb-4">"{{ $item['text'] }}"</p>
                    <div class="flex items-center gap-3">
                        @if($photo)
                            <img src="{{ $photo }}" alt="{{ $item['name'] }}" class="w-10 h-10 rounded-full object-cover" loading="lazy">
                        @else
                            <span class="w-10 h-10 rounded-full brand-bg text-white flex items-center justify-center font-bold">{{ mb_substr($item['name'], 0, 1) }}</span>
                        @endif
                        <div>
                            <div class="font-semibold text-sm">{{ $item['name'] }}</div>
                            @if(!empty($item['place']))<div class="text-xs text-gray-500">{{ $item['place'] }}</div>@endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
