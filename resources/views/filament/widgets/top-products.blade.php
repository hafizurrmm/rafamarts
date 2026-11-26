<x-filament-widgets::widget>
    <x-filament::section heading="টপ সেলিং প্রোডাক্ট" description="বিক্রিত ইউনিট অনুযায়ী, বাতিল ও রিটার্ন বাদে">
        @forelse($rows as $r)
            <div style="padding:10px 0;border-bottom:1px solid rgba(128,128,128,.15)">
                <div style="display:flex;justify-content:space-between;gap:12px;font-size:14px">
                    <span style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500" title="{{ $r->name }}">{{ $r->name }}</span>
                    <span style="white-space:nowrap;opacity:.75">{{ (int) $r->units }} পিস · ৳{{ number_format($r->revenue, 0) }}</span>
                </div>
                <div style="height:6px;border-radius:99px;background:rgba(128,128,128,.18);margin-top:6px">
                    <div style="height:6px;border-radius:99px;background:#0a74b8;width:{{ round(($r->units / $max) * 100) }}%"></div>
                </div>
            </div>
        @empty
            <p style="opacity:.6;font-size:14px">এখনো কোনো বিক্রি নেই।</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
