<?php
// cPanel (শেয়ার্ড হোস্টিং) এর জন্য: এই ফাইলটা public_html/index.php নামে বসবে।
// 'APP_DIR' এর জায়গায় অ্যাপ ফোল্ডারের নাম দিন (যেমন rafamarts)। অ্যাপ থাকবে public_html/APP_DIR এ।

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/APP_DIR/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/APP_DIR/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/APP_DIR/bootstrap/app.php';

$app->handleRequest(Request::capture());
