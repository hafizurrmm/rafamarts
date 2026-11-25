<?php

namespace App\Http\Controllers;

use App\Support\Installer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * ওয়েব ইনস্টলার (/install) — SSH/Cron ছাড়া cPanel এ সেটআপ।
 * ⚠️ এই রুট 'web' মিডলওয়্যার (সেশন/CSRF) ছাড়া চলে, কারণ তখনো .env/ডাটাবেস নেই।
 * নিরাপত্তা: storage/app/install-token ফাইলের গোপন কোড লাগে (শুধু যার ফাইল-অ্যাক্সেস আছে সে জানে);
 * ইনস্টল শেষে পেজ নিজেই বন্ধ হয়।
 */
class InstallController extends Controller
{
    public function show()
    {
        abort_if(Installer::installed(), 404);

        $tokenReady = Installer::ensureToken();

        return view('install.form', [
            'checks' => Installer::checks(),
            'checksPass' => Installer::checksPass(),
            'tokenReady' => $tokenReady,
            'tokenPath' => 'storage/app/install-token',
            'errors' => [],
            'old' => ['app_url' => request()->getSchemeAndHttpHost(), 'db_host' => 'localhost', 'plan' => ''],
            'done' => null,
        ]);
    }

    public function run(Request $request)
    {
        abort_if(Installer::installed(), 404);

        $old = $request->except(['db_pass', 'admin_password', 'admin_password_confirmation', 'token']);
        $fail = fn (array $errors) => response(view('install.form', [
            'checks' => Installer::checks(),
            'checksPass' => Installer::checksPass(),
            'tokenReady' => Installer::ensureToken(),
            'tokenPath' => 'storage/app/install-token',
            'errors' => $errors,
            'old' => $old,
            'done' => null,
        ]), 422);

        if (! Installer::checksPass()) {
            return $fail(['সার্ভারের শর্ত পূরণ হয়নি — উপরের লাল চিহ্নগুলো ঠিক করুন।']);
        }

        if (! Installer::tokenMatches((string) $request->input('token'))) {
            sleep(1); // ব্রুট-ফোর্স ধীর করতে
            Installer::ensureToken();

            return $fail(['ইনস্টল কোড মেলেনি। cPanel File Manager এ ' . 'storage/app/install-token ফাইলের লেখাটা হুবহু বসান।']);
        }

        $in = fn (string $k) => trim((string) $request->input($k, ''));
        $pass = (string) $request->input('db_pass', '');
        $adminPass = (string) $request->input('admin_password', '');
        $errors = [];

        $host = $in('db_host') ?: 'localhost';
        $port = $in('db_port') ?: '3306';
        $dbName = $in('db_name');
        $dbUser = $in('db_user');
        $site = $in('site_name');
        $email = $in('admin_email');
        $adminName = $in('admin_name') ?: 'Admin';
        $phone = $in('phone');
        $url = rtrim($in('app_url'), '/');
        $plan = $in('plan');
        $expires = $in('expires_at');

        if (! preg_match('/^[A-Za-z0-9._-]+$/', $host)) { $errors[] = 'ডাটাবেস হোস্ট ঠিক নয় (সাধারণত localhost)।'; }
        if (! ctype_digit($port)) { $errors[] = 'পোর্ট শুধু সংখ্যা হবে।'; }
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $dbName)) { $errors[] = 'ডাটাবেসের নাম দিন (cPanel এর নামসহ, যেমন user_shop)।'; }
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $dbUser)) { $errors[] = 'ডাটাবেস ইউজার দিন (cPanel এর নামসহ)।'; }
        if (preg_match("/['\\\\\\n\\r]/", $pass)) { $errors[] = "ডাটাবেস পাসওয়ার্ডে ' বা \\ বা নতুন লাইন থাকলে এখানে চলবে না — cPanel এ পাসওয়ার্ড বদলে নিন।"; }
        if ($site === '' || preg_match("/['\\\\\\n\\r]/", $site)) { $errors[] = "দোকানের নাম দিন (' বা \\ ছাড়া)।"; }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'সঠিক অ্যাডমিন ইমেইল দিন।'; }
        if (mb_strlen($adminPass) < 8) { $errors[] = 'অ্যাডমিন পাসওয়ার্ড কমপক্ষে ৮ অক্ষর হবে।'; }
        if ($adminPass !== (string) $request->input('admin_password_confirmation')) { $errors[] = 'অ্যাডমিন পাসওয়ার্ড দুইবার একই হয়নি।'; }
        if (! preg_match('~^https?://[A-Za-z0-9.:_-]+$~', $url)) { $errors[] = 'সাইটের ঠিকানা (URL) https://example.com ধরনের হবে, শেষে / ছাড়া।'; }
        if ($plan !== '' && ! in_array($plan, ['monthly', 'yearly'], true)) { $errors[] = 'প্ল্যান ঠিক নয়।'; }
        if ($expires !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires)) { $errors[] = 'মেয়াদের তারিখ YYYY-MM-DD ফরম্যাটে দিন।'; }
        if ($phone !== '' && ! preg_match('/^[0-9+ -]{6,20}$/', $phone)) { $errors[] = 'ফোন নম্বর ঠিক নয়।'; }

        if ($errors) {
            return $fail($errors);
        }

        // .env লেখার আগে ডাটাবেসে আসলেই সংযোগ হচ্ছে কি না যাচাই
        try {
            new \PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $dbUser, $pass, [
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\Throwable $e) {
            return $fail(['ডাটাবেসে সংযোগ হয়নি: ' . $e->getMessage() . ' — নাম, ইউজার ও পাসওয়ার্ড ঠিক আছে কি না এবং ইউজারকে ডাটাবেসে সব অনুমতি দেওয়া হয়েছে কি না দেখুন।']);
        }

        // .env বানানো (.env.example থেকে)
        $key = 'base64:' . base64_encode(random_bytes(32));
        $env = is_file(base_path('.env.example')) ? (string) file_get_contents(base_path('.env.example')) : '';
        $values = [
            'APP_NAME' => $site, 'APP_ENV' => 'production', 'APP_DEBUG' => 'false', 'APP_KEY' => $key, 'APP_URL' => $url,
            'DB_CONNECTION' => 'mysql', 'DB_HOST' => $host, 'DB_PORT' => $port,
            'DB_DATABASE' => $dbName, 'DB_USERNAME' => $dbUser, 'DB_PASSWORD' => $pass,
            'SESSION_DRIVER' => 'file', 'CACHE_STORE' => 'file',
        ];
        if ($plan !== '') { $values['SUBSCRIPTION_PLAN'] = $plan; }
        if ($expires !== '') { $values['SUBSCRIPTION_EXPIRES_AT'] = $expires; }
        if ($phone !== '') { $values['SUPPORT_PHONE'] = $phone; }

        foreach ($values as $k => $v) {
            $line = $k . "='" . $v . "'";
            $env = preg_match('/^' . $k . '=.*$/m', $env)
                ? preg_replace_callback('/^' . $k . '=.*$/m', fn () => $line, $env, 1)
                : rtrim($env) . "\n" . $line . "\n";
        }

        if (is_file(base_path('.env'))) {
            @copy(base_path('.env'), base_path('.env.backup-' . date('Ymd-His')));
        }
        if (@file_put_contents(base_path('.env'), $env) === false) {
            return $fail(['.env ফাইল লেখা যায়নি — অ্যাপ ফোল্ডারের লেখার অনুমতি দেখুন।']);
        }
        @chmod(base_path('.env'), 0640);

        // এই রিকোয়েস্টেই নতুন কনফিগ ব্যবহার করে টেবিল, অ্যাডমিন ও ডিফল্ট পেজ বানানো
        config([
            'app.key' => $key,
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $dbName,
            'database.connections.mysql.username' => $dbUser,
            'database.connections.mysql.password' => $pass,
            'cache.default' => 'file',
            'session.driver' => 'file',
        ]);
        DB::purge('mysql');

        try {
            $args = ['--site-name' => $site, '--email' => $email, '--name' => $adminName, '--password' => $adminPass];
            if ($phone !== '') {
                $args['--phone'] = $phone;
            }
            $code = Artisan::call('shop:install', $args);
            $output = Artisan::output();
        } catch (\Throwable $e) {
            return $fail(['সেটআপ মাঝপথে থেমেছে: ' . $e->getMessage() . ' — ঠিক করে আবার চেষ্টা করুন (এটা নিরাপদ, আবার চালানো যায়)।']);
        }

        if ($code !== 0) {
            return $fail(['সেটআপ ব্যর্থ হয়েছে: ' . trim(strip_tags($output)) . ' — ঠিক করে আবার চেষ্টা করুন।']);
        }

        @file_put_contents(Installer::lockFile(), date('c'));
        @unlink(Installer::tokenFile());

        return view('install.form', [
            'checks' => [], 'checksPass' => true, 'tokenReady' => true, 'tokenPath' => '',
            'errors' => [], 'old' => [],
            'done' => ['admin_url' => $url . '/admin', 'email' => $email, 'site' => $site],
        ]);
    }
}
