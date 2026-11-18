<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// কাস্টমার যে IP থেকে অর্ডার দিয়েছে সেটা সেভ রাখা (ফেক/বারবার অর্ডার ধরার জন্য)
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_ip', 45)->nullable()->after('customer_note')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_ip']);
            $table->dropColumn('customer_ip');
        });
    }
};
