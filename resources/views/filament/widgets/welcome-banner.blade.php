<x-filament-widgets::widget>
    <div style="background:linear-gradient(135deg,#0a4fa6 0%,#0a74b8 55%,#0894ab 100%);border-radius:16px;padding:24px 28px;color:#fff;position:relative;overflow:hidden">
        <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start">
            <div style="min-width:0">
                <span style="display:inline-block;background:rgba(255,255,255,.16);border-radius:999px;padding:3px 12px;font-size:12px;font-weight:600">● লাইভ · {{ $date }}</span>
                <h2 style="font-size:26px;font-weight:700;margin:12px 0 6px;line-height:1.25">{{ $greeting }}, {{ $name }} 👋</h2>
                <p style="opacity:.92;font-size:14px;line-height:1.6;max-width:640px">
                    {{ $shop }} আজ <b>{{ $todayOrders }}টি</b> অর্ডার পেয়েছে@if($pct !== null) — এই মাসের বিক্রি গত মাসের একই সময়ের চেয়ে <b>{{ abs($pct) }}% {{ $pct >= 0 ? 'বেশি' : 'কম' }}</b>@endif।
                    @if($pending > 0) <b>{{ $pending }}টি</b> অর্ডার কনফার্মের অপেক্ষায়।@endif
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ $ordersUrl }}" style="background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);color:#fff;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none">সব অর্ডার</a>
                <a href="{{ $newProductUrl }}" style="background:#fff;color:#0a4fa6;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none">+ নতুন প্রোডাক্ট</a>
            </div>
        </div>
        <div style="display:flex;gap:36px;flex-wrap:wrap;margin-top:20px">
            <div><div style="font-size:11px;letter-spacing:.06em;opacity:.8">আজকের বিক্রি</div><div style="font-size:24px;font-weight:700">{{ $todaySales }}</div></div>
            <div><div style="font-size:11px;letter-spacing:.06em;opacity:.8">আজকের অর্ডার</div><div style="font-size:24px;font-weight:700">{{ $todayOrders }}</div></div>
            <div><div style="font-size:11px;letter-spacing:.06em;opacity:.8">কনফার্মের অপেক্ষায়</div><div style="font-size:24px;font-weight:700">{{ $pending }}</div></div>
        </div>
    </div>
</x-filament-widgets::widget>
