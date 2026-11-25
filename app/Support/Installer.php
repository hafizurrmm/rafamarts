<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/** ওয়েব ইনস্টলারের সাহায্যকারী: ইনস্টল হয়েছে কি না, টোকেন ফাইল, সার্ভার চেক */
class Installer
{
    public static function lockFile(): string
    {
        return storage_path('app/installed.lock');
    }

    public static function tokenFile(): string
    {
        return storage_path('app/install-token');
    }

    /**
     * ইনস্টল হয়ে থাকলে true (তখন /install বন্ধ)।
     * নিরাপত্তার জন্য সন্দেহ হলে (APP_KEY আছে কিন্তু ডাটাবেস ধরা যাচ্ছে না) true ধরা হয়।
     */
    public static function installed(): bool
    {
        if (is_file(self::lockFile())) {
            return true;
        }

        if (blank(config('app.key'))) {
            return false;
        }

        try {
            return Schema::hasTable('users') && \App\Models\User::where('role', 'admin')->exists();
        } catch (\Throwable $e) {
            return true;
        }
    }

    /** টোকেন ফাইল না থাকলে বানায়। টোকেন কখনো পেজে দেখানো হয় না — মালিক cPanel File Manager থেকে পড়বেন। */
    public static function ensureToken(): bool
    {
        if (is_file(self::tokenFile())) {
            return true;
        }

        return @file_put_contents(self::tokenFile(), bin2hex(random_bytes(16))) !== false;
    }

    public static function tokenMatches(string $given): bool
    {
        $real = is_file(self::tokenFile()) ? trim((string) file_get_contents(self::tokenFile())) : '';

        return $real !== '' && hash_equals($real, trim($given));
    }

    /** @return array<int, array{ok: bool, label: string}> */
    public static function checks(): array
    {
        $rows = [];

        $rows[] = [
            'ok' => version_compare(PHP_VERSION, '8.4.1', '>='),
            'label' => 'PHP ৮.৪.১ বা তার বেশি (এখন ' . PHP_VERSION . ')',
        ];

        foreach (['mbstring', 'intl', 'gd', 'zip', 'bcmath', 'pdo_mysql', 'fileinfo', 'dom', 'curl', 'xml'] as $ext) {
            $rows[] = ['ok' => extension_loaded($ext), 'label' => "PHP এক্সটেনশন: {$ext}"];
        }

        foreach ([storage_path('app'), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache'), base_path()] as $dir) {
            $rows[] = [
                'ok' => is_dir($dir) && is_writable($dir),
                'label' => 'লেখার অনুমতি: ' . str_replace(base_path(), '…', $dir),
            ];
        }

        return $rows;
    }

    public static function checksPass(): bool
    {
        foreach (self::checks() as $row) {
            if (! $row['ok']) {
                return false;
            }
        }

        return true;
    }
}
