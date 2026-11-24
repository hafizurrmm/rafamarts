@php($record = $getRecord())
<div class="px-3 py-2 text-sm leading-tight">
    <div class="font-semibold">{{ $record->order_number }}</div>
    <div class="text-gray-500 dark:text-gray-400 mt-0.5" style="white-space: nowrap">
        {{ $record->created_at?->format('d M, h:i A') }}
        {{-- নতুন/রিপিট কাস্টমার — আলাদা লাইন না নিয়ে তারিখের পাশে (Tailwind রঙ কম্পাইল হয় না বলে inline রঙ) --}}
        @if(($record->prior_orders ?? 0) == 0)
            <span style="margin-left:.35rem;font-size:.7rem;font-weight:600;color:#059669">● New</span>
        @else
            <span style="margin-left:.35rem;font-size:.7rem;font-weight:600;color:#0284c7">● Repeat {{ $record->prior_orders }}</span>
        @endif
    </div>
</div>
