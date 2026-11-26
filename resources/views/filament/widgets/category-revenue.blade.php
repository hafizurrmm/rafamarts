<x-filament-widgets::widget>
    <x-filament::section heading="ক্যাটাগরি অনুযায়ী আয়" description="বাতিল ও রিটার্ন বাদে">
        @forelse($rows as $r)
            @php $pct = round(($r->revenue / $total) * 100, 1); @endphp
            <div style="padding:10px 0;border-bottom:1px solid rgba(128,128,128,.15)">
                <div style="display:flex;justify-content:space-between;gap:12px;font-size:14px">
                    <span style="font-weight:500">{{ $r->name ?: 'ক্যাটাগরি নেই' }}</span>
                    <span style="font-weight:700">{{ $pct }}%</span>
                </div>
                <div style="font-size:12px;opacity:.65">৳{{ number_format($r->revenue, 0) }}</div>
            </div>
        @empty
            <p style="opacity:.6;font-size:14px">এখনো কোনো বিক্রি নেই।</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
