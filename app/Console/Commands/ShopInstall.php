<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * নতুন ক্লায়েন্টের সাইটে এক কমান্ডে প্রাথমিক সেটআপ:
 *
 *   php artisan shop:install --site-name="ক্লায়েন্টের দোকান" --email=admin@client.com --phone=01XXXXXXXXX
 *
 * বারবার চালালেও নিরাপদ: আগে থেকে থাকা অ্যাডমিনের পাসওয়ার্ড বদলায় না (--reset-password না দিলে),
 * আগে থেকে থাকা পেজ ও সেটিংস মুছে না।
 */
class ShopInstall extends Command
{
    protected $signature = 'shop:install
        {--site-name= : দোকানের নাম}
        {--email= : অ্যাডমিনের ইমেইল (লগইনে ব্যবহার হবে)}
        {--name=Admin : অ্যাডমিনের নাম}
        {--password= : পাসওয়ার্ড (না দিলে নিজে থেকে বানানো হবে ও একবার দেখানো হবে)}
        {--phone= : হটলাইন/WhatsApp নম্বর}
        {--no-pages : ড্রাফট পেজ (About, Privacy ইত্যাদি) বানাবে না}
        {--reset-password : অ্যাডমিন আগে থেকে থাকলেও পাসওয়ার্ড নতুন করে বসাবে}';

    protected $description = 'নতুন ক্লায়েন্টের জন্য ডাটাবেস, অ্যাডমিন, ডিফল্ট সেটিংস ও ড্রাফট পেজ সেটআপ করে';

    /** সেটআপের আগে সার্ভারের শর্ত যাচাই — ফেল করলে পরিষ্কার বাংলা বার্তায় থামে */
    protected function preflight(): bool
    {
        $problems = [];

        if (version_compare(PHP_VERSION, '8.4.1', '<')) {
            $problems[] = 'PHP 8.4.1 বা তার বেশি লাগবে (এখন ' . PHP_VERSION . ')। cPanel → MultiPHP Manager থেকে ভার্সন বদলান।';
        }

        $missing = array_filter(
            ['mbstring', 'intl', 'gd', 'zip', 'bcmath', 'pdo_mysql', 'fileinfo', 'dom', 'curl', 'xml'],
            fn ($ext) => ! extension_loaded($ext)
        );
        if ($missing) {
            $problems[] = 'PHP এক্সটেনশন চালু নেই: ' . implode(', ', $missing) . ' (cPanel → Select PHP Version → Extensions)।';
        }

        foreach ([storage_path(), storage_path('logs'), base_path('bootstrap/cache')] as $dir) {
            if (! is_dir($dir) || ! is_writable($dir)) {
                $problems[] = "ফোল্ডার লেখার অনুমতি নেই: {$dir} (775 দিন)।";
            }
        }

        if (! file_exists(base_path('.env'))) {
            $problems[] = '.env ফাইল নেই — .env.example কপি করে .env বানিয়ে DB তথ্য দিন।';
        }

        try {
            \DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $problems[] = 'ডাটাবেসে সংযোগ হয়নি — .env এর DB_DATABASE / DB_USERNAME / DB_PASSWORD ঠিক আছে কি না দেখুন।';
        }

        foreach ($problems as $p) {
            $this->error('✗ ' . $p);
        }

        return ! $problems;
    }

    public function handle(): int
    {
        if (! $this->preflight()) {
            $this->error('উপরের সমস্যাগুলো ঠিক করে আবার চালান।');

            return self::FAILURE;
        }

        $email = $this->option('email') ?: $this->ask('অ্যাডমিনের ইমেইল');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('সঠিক ইমেইল দিন।');

            return self::FAILURE;
        }

        $siteName = $this->option('site-name') ?: $this->ask('দোকানের নাম', 'My Shop');
        $phone = $this->option('phone') ?: null;

        if (blank(config('app.key'))) {
            $this->warn('APP_KEY খালি, নতুন কী বানানো হচ্ছে (.env লেখার অনুমতি লাগবে)।');
            $this->call('key:generate', ['--force' => true]);
        }

        $this->info('১) ডাটাবেস টেবিল তৈরি...');
        $this->call('migrate', ['--force' => true]);

        $this->info('২) অ্যাডমিন ইউজার...');
        $password = null;
        $admin = User::where('email', $email)->first();

        if (! $admin) {
            $password = $this->option('password') ?: Str::password(14, symbols: false);
            User::create([
                'name' => $this->option('name'),
                'email' => $email,
                'password' => $password,
                'role' => 'admin',
            ]);
            $this->line("   নতুন অ্যাডমিন বানানো হয়েছে: {$email}");
        } else {
            if ($admin->role !== 'admin') {
                $admin->update(['role' => 'admin']);
            }
            if ($this->option('reset-password')) {
                $password = $this->option('password') ?: Str::password(14, symbols: false);
                $admin->update(['password' => $password]);
                $this->line('   পাসওয়ার্ড নতুন করে বসানো হয়েছে।');
            } else {
                $this->line('   এই ইমেইলের অ্যাডমিন আগে থেকেই আছে, পাসওয়ার্ড বদলানো হয়নি।');
            }
        }

        $this->info('৩) ডিফল্ট সেটিংস...');
        $defaults = ['site_name' => $siteName, 'cod_enabled' => '1'];
        if ($phone) {
            $defaults['hotline_number'] = $phone;
            $defaults['whatsapp_number'] = $phone;
        }

        foreach ($defaults as $key => $value) {
            // শুধু যেগুলো এখনো সেট করা হয়নি সেগুলো বসাবে, আগের সেটিং ওভাররাইট করবে না
            if (! Setting::where('key', $key)->exists() || ($key === 'site_name' && $this->option('site-name'))) {
                Setting::set($key, $value);
            }
        }

        if (! $this->option('no-pages')) {
            $this->info('৪) ড্রাফট পেজ ও ফুটার মেনু...');
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\DefaultPagesSeeder', '--force' => true]);
        }

        $this->info('৫) ক্যাশ পরিষ্কার...');
        $this->call('optimize:clear');

        $this->newLine();
        $this->info('সেটআপ শেষ।');
        $this->line('অ্যাডমিন লগইন: ' . rtrim((string) config('app.url'), '/') . '/admin');
        $this->line('ইমেইল: ' . $email);

        if ($password) {
            $this->warn("পাসওয়ার্ড (এখনই লিখে রাখুন, আর দেখানো হবে না): {$password}");
        }

        $this->line('এরপর: Store Settings-এ লোগো, পেমেন্ট নম্বর, Telegram ও Pixel দিন; ড্রাফট পেজের লেখা ঠিক করে Publish করুন।');

        return self::SUCCESS;
    }
}
