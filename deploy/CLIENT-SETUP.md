# NexMart — নতুন ক্লায়েন্টের সাইট বসানো (cPanel)

> ⚠️ এই গাইড ও `shop:install` কমান্ড লাইভ সার্ভারে এখনো পুরোপুরি পরীক্ষা করা হয়নি।
> প্রথম ক্লায়েন্টের আগে একটা টেস্ট সাবডোমেইনে পুরো ধাপ একবার চালিয়ে দেখুন।

## সার্ভারের শর্ত
- **PHP 8.4.1 বা তার বেশি** (`composer.lock` এর Symfony 8 লাইব্রেরির কারণে; PHP 8.3 এ চলবে না)।
- MySQL/MariaDB, PHP এক্সটেনশন: `mbstring intl gd zip bcmath pdo_mysql fileinfo dom curl xml`।
- cPanel → **MultiPHP Manager** থেকে ডোমেইনে PHP ভার্সন ঠিক করুন।

## প্রতি ক্লায়েন্টের তথ্য (আগে লিখে রাখুন)
ডোমেইন, cPanel ইউজার, PHP পাথ (`/usr/local/bin/ea-php84` বা `ea-php85`), ডাটাবেস নাম/ইউজার/পাসওয়ার্ড, অ্যাডমিন ইমেইল, ফোন।

## ধাপ (SSH ছাড়া শেয়ার্ড cPanel)
এখানে `USER` = cPanel ইউজার, `APP_DIR` = অ্যাপ ফোল্ডারের নাম (যেমন `nexmart`)।

1. **ব্যাকআপ**: নতুন সাইট হলে দরকার নেই; আগের সাইট থাকলে ফাইল + ডাটাবেস ব্যাকআপ নিন।
2. **ডাটাবেস**: cPanel → MySQL® Databases → ডাটাবেস ও ইউজার বানিয়ে ইউজারকে সব অনুমতি দিন।
3. **কোড আপলোড**: `public_html/APP_DIR` ফোল্ডার বানান। GitHub Releases (বা Actions → artifact) থেকে `shop-release.zip` ডাউনলোড করে ওই ফোল্ডারে আপলোড ও Extract করুন। zip মুছে দিন।
4. **`.env`**: `APP_DIR/.env.example` কপি করে `.env` নাম দিন। এডিট করুন: `APP_NAME`, `APP_URL` (https সহ), `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`। `APP_KEY` খালি রাখুন।
5. **`public_html` সাজান**:
   - `APP_DIR/deploy/cpanel/index.php` → `public_html/index.php` কপি করে ভেতরের `APP_DIR` শব্দ আসল ফোল্ডারের নামে বদলান।
   - `APP_DIR/deploy/cpanel/public.htaccess` এর লেখা `public_html/.htaccess` ফাইলের **নিচে যোগ করুন** (ভেতরের `APP_DIR` আসল ফোল্ডারের নামে বদলান; ছবির `/storage` পাথ এই রিরাইট রুলেই চলে, symlink লাগে না)। ⚠️ ফাইলটা ওভাররাইট করবেন না: cPanel এতে PHP ভার্সনের কনফিগ লিখে রাখে, মুছলে ভুল PHP ভার্সনে চলবে। `.well-known`, `cgi-bin`, `.user.ini`, `php.ini` ও কিছু মুছবেন না।
   - `APP_DIR/public/` এর ভেতরের `css`, `js`, `fonts`, `images`, `favicon.ico`, `robots.txt` → `public_html/` এ কপি।
   - `APP_DIR/deploy/cpanel/app-folder.htaccess` → `APP_DIR/.htaccess` (অ্যাপ ফোল্ডার ব্রাউজার থেকে বন্ধ)।
   - `APP_DIR/deploy/cpanel/storage-public.htaccess` → `APP_DIR/storage/app/public/.htaccess` (ছবি দেখা যাবে, PHP চলবে না)।
6. **ফোল্ডার অনুমতি**: `APP_DIR/storage` ও `APP_DIR/bootstrap/cache` কে 775 (সাব-ফোল্ডারসহ)।
7. **ইনস্টল (একবারের Cron)**: Cron Jobs → সময় `* * * * *` → Command (এক লাইনে, নিজের তথ্য বসিয়ে):
```
( cd /home/USER/public_html/APP_DIR && /usr/local/bin/ea-php84 artisan shop:install --site-name="দোকানের নাম" --email=admin@client.com --phone=01XXXXXXXXX ) > /home/USER/install.log 2>&1
```
8. ২–৩ মিনিট পর `/home/USER/install.log` খুলে **অ্যাডমিন পাসওয়ার্ড** টুকে নিন। তারপর **Cron জব মুছুন ও `install.log` মুছে ফেলুন** (ফাইলে পাসওয়ার্ড থাকে)।
9. **SSL**: cPanel → SSL/TLS Status → AutoSSL চালান। `APP_URL` https রাখুন।

## ধাপ (SSH/টার্মিনাল আছে)
```
cd ~/public_html/APP_DIR
cp .env.example .env   # এডিট করুন
php artisan shop:install --site-name="..." --email=... --phone=...
```
(বাকি ফাইল সাজানো উপরের ৫ নম্বর ধাপের মতো।)

## চালু করার আগে চেকলিস্ট
- `https://ডোমেইন/APP_DIR/.env` খুললে **403/404** আসতে হবে (ফাইলের লেখা দেখা গেলে এখনই বন্ধ করুন)।
- `APP_DEBUG=false`, `APP_ENV=production`।
- Admin → Store Settings: লোগো, নাম, ফোন, bKash/Nagad/Rocket নম্বর, Telegram, Pixel।
- Website → Pages: ড্রাফট পেজের লেখা ঠিক করে Publish করুন; Menus সাজান।
- টেস্ট অর্ডার (COD ও মোবাইল পেমেন্ট), Telegram মেসেজ, Abandoned Cart, ছবি আপলোড ও দেখানো।
- `DemoSeeder` কখনো চালাবেন না (দুর্বল পাসওয়ার্ডের অ্যাডমিন বানায়; প্রোডাকশনে নিজেই বন্ধ থাকে)।

## আপডেট (নতুন রিলিজ)
1. ফাইল + ডাটাবেস ব্যাকআপ।
2. নতুন `shop-release.zip` আপলোড করে Extract (Overwrite)। `.env`, `storage` মুছবেন না।
3. `public_html/` এর `css/js/fonts/images` আবার কপি; `index.php` ও `.htaccess` ছোঁবেন না।
4. Cron/SSH এ: `php artisan migrate --force && php artisan optimize:clear`।
5. সাইট ও অ্যাডমিন ঘুরে দেখুন।

## ক্লায়েন্টকে কী দেবেন না
গিটহাব রিপোর অ্যাক্সেস, আপনার API টোকেন, আপনার `.env`। প্রতি ক্লায়েন্টের নিজের Telegram বট, Pixel ও পেমেন্ট নম্বর।
