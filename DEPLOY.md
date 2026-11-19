# ডিপ্লয়

- **নিজের সাইট (rafamarts.com):** GitHub থেকে cPanel Git/Cron দিয়ে — দেখুন `.cpanel.yml` (এই হোস্টে `rsync` নেই, তাই `cp`)।
- **নতুন ক্লায়েন্টের সাইট:** `deploy/CLIENT-SETUP.md` অনুসরণ করুন; `php artisan shop:install` দিয়ে সেটআপ।
- **রিলিজ zip:** `v1.0.0` ধরনের ট্যাগ পুশ করলে GitHub Actions `vendor` সহ `shop-release.zip` বানায়।
- PHP 8.4.1+ লাগবে।
