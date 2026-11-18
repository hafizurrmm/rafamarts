@php $list = \App\Support\PageBlocks::categoryList($data); @endphp
@if($list->isNotEmpty())
    <section>
        @if(!empty($data['heading']))
            <h2 class="section-heading font-bold mb-4 text-center">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 md:gap-5">
            @foreach($list as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}" class="flex flex-col items-center gap-2 text-center group">
                    <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('images/placeholder-product.png') }}"
                         alt="{{ $category->name }}" class="w-20 h-20 md:w-24 md:h-24 rounded-full object-cover border-2 border-gray-100 group-hover:brand-border" loading="lazy" decoding="async">
                    <span class="text-sm font-medium text-gray-700 group-hover:brand-text">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
