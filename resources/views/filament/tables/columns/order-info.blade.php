@php($record = $getRecord())
<div class="px-3 py-2 text-sm leading-tight">
    <div class="font-semibold">{{ $record->order_number }}</div>
    <div class="text-gray-500 dark:text-gray-400 mt-0.5">{{ $record->created_at?->format('d M, h:i A') }}</div>
    @if(($record->prior_orders ?? 0) == 0)
        <span class="inline-block mt-1 text-xs font-semibold text-white bg-emerald-600 rounded px-1.5 py-0.5">New Customer</span>
    @else
        <span class="inline-block mt-1 text-xs font-semibold text-white bg-sky-600 rounded px-1.5 py-0.5">Repeat · {{ $record->prior_orders }}টা আগের অর্ডার</span>
    @endif
</div>
