<?php

// প্রোডাক্টের ডিফল্ট ব্র্যান্ড নাম। Store Settings-এ site_name দিলে সেটাই ব্যবহার হয়।
return [
    'name' => env('SHOP_NAME', 'NexMart'),
    'tagline' => env('SHOP_TAGLINE', 'Shop More, Live Better'),
];
