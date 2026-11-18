@php $align = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$data['align'] ?? 'left'] ?? 'text-left'; @endphp
<section class="bg-white rounded-2xl border border-gray-100 p-5 md:p-8 {{ $align }}">
    <div class="rich-content text-gray-700">{!! $data['content'] ?? '' !!}</div>
</section>
