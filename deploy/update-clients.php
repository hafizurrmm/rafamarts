<?php
/**
 * সব ক্লায়েন্ট সাইটে একসাথে আপডেট দেওয়ার স্ক্রিপ্ট (cPanel, SSH ছাড়াও চলে)।
 *
 *   php deploy/update-clients.php --dry-run        # কী হবে শুধু দেখাবে, কিছু বদলাবে না
 *   php deploy/update-clients.php                  # সব ক্লায়েন্টে আপডেট
 *   php deploy/update-clients.php --only=rahim     # শুধু একটা ক্লায়েন্ট
 *   php deploy/update-clients.php --clients=/home/USER/my-clients.txt
 *
 * উৎস = এই স্ক্রিপ্ট যে রিপো/ফোল্ডারে আছে (cPanel Git এর রিপোজিটরি ফোল্ডার)।
 * ক্লায়েন্টের তালিকা রিপোর বাইরে রাখুন (ডিফল্ট: ~/nexmart-clients.txt) — গিটে ক্লায়েন্টের তথ্য দেবেন না।
 *
 * তালিকার ফরম্যাট (প্রতি লাইনে একটা ক্লায়েন্ট, # দিয়ে মন্তব্য):
 *   নাম | অ্যাপ ফোল্ডার | public_html ফোল্ডার | PHP পাথ (ঐচ্ছিক)
 *   rahim | /home/USER/public_html/rahim_app | /home/USER/public_html/rahim | /usr/local/bin/ea-php85
 *
 * যা ছোঁয়া হয় না: .env, storage, vendor, composer.lock, public_html/index.php, .htaccess
 */

if (PHP_SAPI !== 'cli') {
    exit("শুধু কমান্ড লাইন/Cron থেকে চলে।\n");
}

$source = realpath(__DIR__ . '/..');
$opts = getopt('', ['dry-run', 'only:', 'clients:']);
$dry = isset($opts['dry-run']);
$only = $opts['only'] ?? null;
$home = rtrim((string) (getenv('HOME') ?: ''), '/');
$listFile = $opts['clients'] ?? ($home . '/nexmart-clients.txt');
$logFile = $home . '/nexmart-update.log';

$appDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes'];
$publicDirs = ['css', 'fonts', 'images', 'js'];

function out(string $msg): void
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    echo $line . "\n";
    @file_put_contents($logFile, $line . "\n", FILE_APPEND);
}

/** ফোল্ডার কপি; $skip এ থাকা টপ-লেভেল নাম বাদ। ফেরত দেয় কপি হওয়া ফাইল সংখ্যা। */
function copyTree(string $from, string $to, bool $dry, array $skip = []): int
{
    if (! is_dir($from)) {
        return 0;
    }
    $count = 0;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $rel = substr($item->getPathname(), strlen($from) + 1);
        $top = explode(DIRECTORY_SEPARATOR, $rel)[0];
        if (in_array($top, $skip, true) || $top === '.git') {
            continue;
        }
        $dest = $to . '/' . $rel;
        if ($item->isDir()) {
            if (! $dry && ! is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
        } else {
            if (! $dry) {
                if (! is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0755, true);
                }
                if (! copy($item->getPathname(), $dest)) {
                    throw new RuntimeException("কপি হয়নি: {$dest}");
                }
            }
            $count++;
        }
    }

    return $count;
}

if (! is_file($listFile)) {
    out("ক্লায়েন্টের তালিকা নেই: {$listFile}");
    out('ফরম্যাট: নাম | অ্যাপ ফোল্ডার | public_html ফোল্ডার | PHP পাথ(ঐচ্ছিক)');
    exit(1);
}

$clients = [];
foreach (file($listFile, FILE_IGNORE_NEW_LINES) as $n => $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    $p = array_map('trim', explode('|', $line));
    if (count($p) < 3) {
        out('লাইন ' . ($n + 1) . ' বাদ (নাম | অ্যাপ ফোল্ডার | public_html ফোল্ডার লাগবে)');
        continue;
    }
    $clients[] = ['name' => $p[0], 'app' => rtrim($p[1], '/'), 'pub' => rtrim($p[2], '/'), 'php' => $p[3] ?? PHP_BINARY];
}

if ($only) {
    $clients = array_values(array_filter($clients, fn ($c) => $c['name'] === $only));
}
if (! $clients) {
    out('কোনো ক্লায়েন্ট পাওয়া যায়নি।');
    exit(1);
}

out(($dry ? '[DRY-RUN] ' : '') . 'আপডেট শুরু: ' . count($clients) . " টি সাইট, উৎস: {$source}");

$ok = 0;
$failed = [];
$warnings = [];

foreach ($clients as $c) {
    $name = $c['name'];
    out("— {$name}");

    try {
        if (realpath($c['app']) === $source) {
            throw new RuntimeException('অ্যাপ ফোল্ডার = উৎস ফোল্ডার; এটা ক্লায়েন্ট নয়');
        }
        foreach (['artisan', '.env', 'vendor/autoload.php'] as $must) {
            if (! file_exists($c['app'] . '/' . $must)) {
                throw new RuntimeException("{$c['app']}/{$must} নেই — এটা কি সঠিক ইনস্টল?");
            }
        }
        if (! is_dir($c['pub'])) {
            throw new RuntimeException("public_html ফোল্ডার নেই: {$c['pub']}");
        }

        // vendor আপডেট দরকার কি না: composer.lock কপি করা হয় না; ক্লায়েন্টেরটা আলাদা হলে সতর্কতা
        $srcLock = $source . '/composer.lock';
        $dstLock = $c['app'] . '/composer.lock';
        if (is_file($srcLock) && is_file($dstLock) && md5_file($srcLock) !== md5_file($dstLock)) {
            $warnings[] = "{$name}: composer.lock আলাদা — লাইব্রেরি (vendor) বদলেছে, নতুন shop-release.zip দিয়ে আপডেট দিন";
            out('  ⚠ composer.lock আলাদা (vendor আপডেট দরকার হতে পারে)');
        }

        $files = 0;
        foreach ($appDirs as $d) {
            $files += copyTree("{$source}/{$d}", "{$c['app']}/{$d}", $dry, $d === 'bootstrap' ? ['cache'] : []);
        }
        foreach ($publicDirs as $d) {
            $files += copyTree("{$source}/public/{$d}", "{$c['pub']}/{$d}", $dry);
        }
        out("  ফাইল কপি: {$files}" . ($dry ? ' (ড্রাই-রান, কিছু লেখা হয়নি)' : ''));

        if (! $dry) {
            foreach (['migrate --force', 'optimize:clear'] as $cmd) {
                $full = 'cd ' . escapeshellarg($c['app']) . ' && ' . escapeshellarg($c['php']) . ' artisan ' . $cmd . ' 2>&1';
                exec($full, $lines, $code);
                $tail = trim(implode("\n", array_slice($lines, -3)));
                $lines = [];
                if ($code !== 0) {
                    throw new RuntimeException("artisan {$cmd} ব্যর্থ (কোড {$code}): {$tail}");
                }
                out("  artisan {$cmd}: ঠিক আছে");
            }
        }
        $ok++;
    } catch (Throwable $e) {
        $failed[] = $name;
        out('  ✗ ব্যর্থ: ' . $e->getMessage());
    }
}

out("শেষ: সফল {$ok}, ব্যর্থ " . count($failed) . ($failed ? ' (' . implode(', ', $failed) . ')' : ''));
foreach ($warnings as $w) {
    out('সতর্কতা: ' . $w);
}

exit($failed ? 1 : 0);
