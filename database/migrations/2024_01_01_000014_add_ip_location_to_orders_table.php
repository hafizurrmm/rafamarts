<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// IP থেকে বের করা লোকেশন — ফেক/বিদেশি অর্ডার চেনার জন্য
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ip_location')->nullable()->after('customer_ip');   // যেমন: Dhaka, Dhaka Division, Bangladesh
            $table->string('ip_country_code', 2)->nullable()->after('ip_location'); // BD, US ...
            $table->string('ip_isp')->nullable()->after('ip_country_code');    // যেমন: Grameenphone / Link3
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ip_location', 'ip_country_code', 'ip_isp']);
        });
    }
};
