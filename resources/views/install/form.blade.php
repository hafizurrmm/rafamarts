<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>NexMart — ইনস্টলার</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f4f7f5;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;color:#0f2a43;padding:16px}
        .w{max-width:680px;margin:24px auto}
        .c{background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.07);padding:24px;margin-bottom:16px}
        h1{font-size:1.4rem;margin:0 0 4px}h2{font-size:1.05rem;margin:0 0 12px}
        .sub{color:#64748b;margin:0 0 8px;font-size:.92rem}
        label{display:block;font-weight:600;font-size:.88rem;margin:12px 0 4px}
        input,select{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:1rem;background:#fff}
        input:focus,select:focus{outline:2px solid #059669;border-color:#059669}
        .g{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}@media(max-width:560px){.g{grid-template-columns:1fr}}
        .hint{font-size:.8rem;color:#64748b;margin-top:3px}
        .err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:.92rem}
        .ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;padding:14px;font-size:.95rem}
        .chk{display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;font-size:.85rem}@media(max-width:560px){.chk{grid-template-columns:1fr}}
        .y{color:#059669}.n{color:#dc2626;font-weight:700}
        button{background:#059669;color:#fff;border:0;border-radius:999px;padding:13px 28px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:18px;width:100%}
        button[disabled]{background:#94a3b8;cursor:not-allowed}
        code{background:#f1f5f9;padding:2px 6px;border-radius:6px;font-size:.85em;word-break:break-all}
    </style>
</head>
<body>
<div class="w">
@if($done)
    <div class="c">
        <h1>✅ ইনস্টল সম্পন্ন!</h1>
        <p class="sub">{{ $done['site'] }} এখন চালু। এই ইনস্টলার পেজ নিজে থেকেই বন্ধ হয়ে গেছে।</p>
        <div class="ok">
            অ্যাডমিন লগইন: <a href="{{ $done['admin_url'] }}"><strong>{{ $done['admin_url'] }}</strong></a><br>
            ইমেইল: <strong>{{ $done['email'] }}</strong><br>
            পাসওয়ার্ড: যেটা আপনি এখানে দিয়েছেন।
        </div>
        <p class="sub" style="margin-top:14px">পরের কাজ: Store Settings এ লোগো, ফোন, পেমেন্ট নম্বর, Telegram ও কুরিয়ার API দিন; Website → Pages এর ড্রাফট লেখা ঠিক করে Publish করুন।</p>
    </div>
@else
    <div class="c">
        <h1>NexMart ইনস্টলার</h1>
        <p class="sub">কয়েকটা তথ্য দিলেই দোকান চালু হয়ে যাবে — Cron বা ফাইল এডিট লাগবে না।</p>
    </div>

    <div class="c">
        <h2>১) সার্ভার পরীক্ষা</h2>
        <div class="chk">
            @foreach($checks as $row)
                <div class="{{ $row['ok'] ? 'y' : 'n' }}">{{ $row['ok'] ? '✓' : '✗' }} {{ $row['label'] }}</div>
            @endforeach
        </div>
        @unless($checksPass)
            <p class="n" style="margin-top:10px">লাল চিহ্নগুলো ঠিক করে পেজ রিফ্রেশ করুন (cPanel → MultiPHP Manager / Select PHP Version / File Manager → Permissions 775)।</p>
        @endunless
    </div>

    <form method="POST" action="{{ url('/install') }}" autocomplete="off">
        @if($errors)
            <div class="err">@foreach($errors as $e)<div>• {{ $e }}</div>@endforeach</div>
        @endif

        <div class="c">
            <h2>২) ইনস্টল কোড (নিরাপত্তার জন্য)</h2>
            <p class="sub">cPanel → File Manager → অ্যাপ ফোল্ডারের <code>{{ $tokenPath }}</code> ফাইলটি খুলুন, ভেতরের লেখাটা (৩২ অক্ষর) এখানে বসান। এতে অন্য কেউ আপনার আগে ইনস্টল করতে পারবে না।</p>
            @unless($tokenReady)<div class="err">কোড ফাইল বানানো যায়নি — <code>storage/app</code> ফোল্ডারের লেখার অনুমতি দিন।</div>@endunless
            <label>ইনস্টল কোড</label>
            <input name="token" required autocomplete="off">
        </div>

        <div class="c">
            <h2>৩) ডাটাবেস (cPanel → MySQL® Databases)</h2>
            <div class="g">
                <div><label>ডাটাবেসের নাম</label><input name="db_name" value="{{ $old['db_name'] ?? '' }}" required placeholder="cpuser_shop"></div>
                <div><label>ডাটাবেস ইউজার</label><input name="db_user" value="{{ $old['db_user'] ?? '' }}" required placeholder="cpuser_shopuser"></div>
                <div><label>ডাটাবেস পাসওয়ার্ড</label><input name="db_pass" type="password" autocomplete="new-password"></div>
                <div><label>হোস্ট</label><input name="db_host" value="{{ $old['db_host'] ?? 'localhost' }}" required><div class="hint">সাধারণত localhost</div></div>
            </div>
        </div>

        <div class="c">
            <h2>৪) দোকান ও অ্যাডমিন</h2>
            <div class="g">
                <div><label>দোকানের নাম</label><input name="site_name" value="{{ $old['site_name'] ?? '' }}" required></div>
                <div><label>সাইটের ঠিকানা (URL)</label><input name="app_url" value="{{ $old['app_url'] ?? '' }}" required></div>
                <div><label>অ্যাডমিনের নাম</label><input name="admin_name" value="{{ $old['admin_name'] ?? 'Admin' }}"></div>
                <div><label>অ্যাডমিন ইমেইল (লগইনে লাগবে)</label><input name="admin_email" type="email" value="{{ $old['admin_email'] ?? '' }}" required></div>
                <div><label>অ্যাডমিন পাসওয়ার্ড (কমপক্ষে ৮ অক্ষর)</label><input name="admin_password" type="password" required autocomplete="new-password"></div>
                <div><label>পাসওয়ার্ড আবার</label><input name="admin_password_confirmation" type="password" required autocomplete="new-password"></div>
                <div><label>ফোন / WhatsApp (ঐচ্ছিক)</label><input name="phone" value="{{ $old['phone'] ?? '' }}" placeholder="01XXXXXXXXX"></div>
            </div>
        </div>

        <div class="c">
            <h2>৫) সাবস্ক্রিপশন (ঐচ্ছিক — হোস্টেড ক্লায়েন্টের জন্য)</h2>
            <div class="g">
                <div><label>প্ল্যান</label>
                    <select name="plan">
                        <option value="" @selected(($old['plan'] ?? '') === '')>— সীমাহীন —</option>
                        <option value="monthly" @selected(($old['plan'] ?? '') === 'monthly')>মাসিক</option>
                        <option value="yearly" @selected(($old['plan'] ?? '') === 'yearly')>বার্ষিক</option>
                    </select></div>
                <div><label>মেয়াদ শেষ (তারিখ)</label><input name="expires_at" type="date" value="{{ $old['expires_at'] ?? '' }}"></div>
            </div>
            <p class="hint">মেয়াদ দিলে শেষ হওয়ার পর অ্যাডমিন বন্ধ হবে (দোকান চালু থাকবে)। পরে .env থেকে বদলানো যাবে।</p>
        </div>

        <button type="submit" @disabled(! $checksPass)>ইনস্টল করুন</button>
    </form>
@endif
</div>
</body>
</html>
