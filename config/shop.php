<?php

// প্রোডাক্টের ডিফল্ট ব্র্যান্ড নাম। Store Settings-এ site_name দিলে সেটাই ব্যবহার হয়।
return [
    'name' => env('SHOP_NAME', 'NexMart'),
    'tagline' => env('SHOP_TAGLINE', 'Shop More, Live Better'),

    // সাবস্ক্রিপশন (হোস্টেড SaaS): ক্লায়েন্টের সাইটের .env এ বসান। SUBSCRIPTION_EXPIRES_AT খালি = সীমাহীন (আপনার নিজের সাইট)।
    'subscription' => [
        'plan' => env('SUBSCRIPTION_PLAN'),                 // monthly | yearly (শুধু দেখানোর জন্য)
        'expires_at' => env('SUBSCRIPTION_EXPIRES_AT'),     // YYYY-MM-DD, ওই দিনের শেষ পর্যন্ত চালু
        'grace_days' => (int) env('SUBSCRIPTION_GRACE_DAYS', 3),
        'warn_days' => (int) env('SUBSCRIPTION_WARN_DAYS', 7),
        'support_phone' => env('SUPPORT_PHONE'),
    ],
];
