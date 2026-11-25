<?php

namespace App\Console\Commands;

use App\Services\CourierSync;
use Illuminate\Console\Command;

class CourierSyncCommand extends Command
{
    protected $signature = 'courier:sync {--limit=50 : এক রানে সর্বোচ্চ কত অর্ডার চেক} {--days=45 : কত দিন আগের অর্ডার পর্যন্ত} {--force : অটো-সিঙ্ক বন্ধ থাকলেও চালাবে}';

    protected $description = 'চলমান অর্ডারগুলোর কুরিয়ার স্ট্যাটাস কুরিয়ারের API থেকে আপডেট করে';

    public function handle(CourierSync $sync): int
    {
        if (! $this->option('force') && ! CourierSync::enabled()) {
            $this->line('কুরিয়ার অটো-সিঙ্ক বন্ধ আছে (Courier API পেজে চালু করুন)।');

            return self::SUCCESS;
        }

        $s = $sync->run(max(1, (int) $this->option('limit')), max(1, (int) $this->option('days')));
        $this->info("চেক {$s['checked']}, বদলেছে {$s['changed']}, Completed {$s['completed']}, Returned {$s['returned']}, ত্রুটি {$s['errors']}, বাদ {$s['skipped']}");

        return self::SUCCESS;
    }
}
