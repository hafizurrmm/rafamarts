<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// কুরিয়ার স্ট্যাটাস অটো-সিঙ্ক: কোন অর্ডার সর্বশেষ কখন চেক হয়েছে (পালা করে চেক করার জন্য)
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'courier_synced_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('courier_synced_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'courier_synced_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('courier_synced_at');
            });
        }
    }
};
