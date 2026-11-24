@php($record = $getRecord())
<div class="px-3 py-2 text-sm leading-snug min-w-[11rem]">
    <div class="font-semibold">{{ $record->customer_name }}</div>
    {{-- ফোন নম্বরে ক্লিক করলে ফোনে/ডেস্কটপের কলিং অ্যাপে সরাসরি কল ওপেন হবে --}}
    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $record->customer_phone) }}"
       onclick="event.stopPropagation()"
       class="font-medium hover:underline" style="color:#7c3aed">{{ $record->customer_phone }}</a>
    @if($record->customer_ip)
        {{-- বাংলাদেশের বাইরের IP হলে সতর্কতা (VPN/বিদেশি/ফেক হওয়ার সম্ভাবনা বেশি) --}}
        @if($record->ip_country_code && $record->ip_country_code !== 'BD')
            <span class="inline-block mt-0.5 text-xs font-semibold text-white bg-red-600 rounded px-1.5 py-0.5">⚠ বিদেশি IP</span>
        @endif
    @endif
</div>

{{-- Filament কাস্টম ভিউতে সব Tailwind ক্লাস কম্পাইল হয় না, তাই রঙ সরাসরি CSS দিয়ে দেওয়া হয়েছে --}}
<style>
    .order-address { color: #4b5563; }
    .dark .order-address { color: #d1d5db; }
    .order-ip { color: #6b7280; }
    .dark .order-ip { color: #9ca3af; }
</style>
