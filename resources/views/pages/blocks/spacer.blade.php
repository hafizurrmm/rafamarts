@php $h = ['sm' => 'h-4', 'md' => 'h-8', 'lg' => 'h-16'][$data['size'] ?? 'md'] ?? 'h-8'; @endphp
<div class="{{ $h }} flex items-center" aria-hidden="true">
    @if(!empty($data['line']))<hr class="w-full border-gray-200">@endif
</div>
