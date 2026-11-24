@php $sub = \App\Support\Subscription::state(); @endphp
@if(in_array($sub['status'], ['expiring', 'grace'], true))
    @php
        $grace = $sub['status'] === 'grace';
        $phone = config('shop.subscription.support_phone');
    @endphp
    <div style="margin-bottom:1rem;padding:.75rem 1rem;border-radius:.75rem;font-size:.9rem;line-height:1.5;border:1px solid {{ $grace ? '#fecaca' : '#fde68a' }};background:{{ $grace ? '#fef2f2' : '#fffbeb' }};color:{{ $grace ? '#991b1b' : '#92400e' }}">
        @if($grace)
            <strong>সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে।</strong>
            {{ $sub['grace_ends']->format('d M Y') }} এর মধ্যে নবায়ন না করলে অ্যাডমিন প্যানেল বন্ধ হয়ে যাবে।
        @else
            <strong>সাবস্ক্রিপশন নবায়নের সময় হয়ে আসছে।</strong>
            মেয়াদ শেষ হবে {{ $sub['expires_at']->format('d M Y') }} ({{ $sub['days_left'] }} দিন বাকি)।
        @endif
        @if($phone) নবায়নে যোগাযোগ: <strong>{{ $phone }}</strong> @endif
    </div>
@endif
