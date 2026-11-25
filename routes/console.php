<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// কুরিয়ার স্ট্যাটাস অটো-সিঙ্ক — cPanel Cron এ প্রতি মিনিটে `php artisan schedule:run` বসাতে হবে
\Illuminate\Support\Facades\Schedule::command('courier:sync')->everyFifteenMinutes()->withoutOverlapping(10);
