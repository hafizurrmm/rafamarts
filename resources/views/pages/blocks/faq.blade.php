@php $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => !empty($i['question']) && !empty($i['answer']))); @endphp
@if($items)
    <section class="max-w-3xl mx-auto w-full">
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="space-y-2">
            @foreach($items as $item)
                <details class="bg-white rounded-xl border border-gray-100 p-4 group">
                    <summary class="font-semibold cursor-pointer flex items-center justify-between gap-3 list-none">
                        <span>{{ $item['question'] }}</span>
                        <span class="text-xl brand-text group-open:rotate-45 transition-transform shrink-0">+</span>
                    </summary>
                    <p class="mt-3 text-gray-600 whitespace-pre-line">{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>
@endif
