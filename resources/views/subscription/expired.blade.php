<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>সাবস্ক্রিপশনের মেয়াদ শেষ</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f7f5;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#0f2a43;padding:16px}
        .c{background:#fff;max-width:440px;width:100%;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.08);padding:32px 24px;text-align:center}
        .i{width:56px;height:56px;border-radius:50%;background:#fef3c7;color:#b45309;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 16px}
        h1{font-size:1.25rem;margin:0 0 8px}p{color:#475569;line-height:1.6;margin:0 0 16px}
        a.b{display:inline-block;background:#059669;color:#fff;text-decoration:none;font-weight:700;padding:12px 24px;border-radius:999px}
        a.s{display:block;margin-top:16px;color:#64748b;font-size:.875rem}
    </style>
</head>
<body>
    <div class="c">
        <div class="i">!</div>
        <h1>অ্যাডমিন প্যানেলের সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে</h1>
        <p>{{ \App\Models\Setting::get('site_name', config('shop.name')) }}-এর অ্যাডমিন প্যানেল আবার চালু করতে সাবস্ক্রিপশন নবায়ন করুন। আপনার দোকান ও অর্ডারের তথ্য নিরাপদ আছে এবং কাস্টমাররা সাইট দেখতে ও অর্ডার করতে পারছে।</p>
        @if($phone = config('shop.subscription.support_phone'))
            <a class="b" href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}">নবায়নের জন্য কল করুন: {{ $phone }}</a>
        @else
            <p><strong>নবায়নের জন্য সার্ভিস প্রদানকারীর সাথে যোগাযোগ করুন।</strong></p>
        @endif
        <a class="s" href="/">← দোকানের হোমপেজ</a>
    </div>
</body>
</html>
