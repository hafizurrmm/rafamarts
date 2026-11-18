<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// bKash / Nagad / Rocket ম্যানুয়াল পেমেন্ট — কাস্টমারের দেওয়া TrxID ও প্রেরকের নম্বর
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // unique: একই TrxID দিয়ে দুটো অর্ডার করা যাবে না (NULL একাধিক থাকতে পারে, তাই COD অর্ডারে সমস্যা নেই)
            $table->string('payment_trx_id', 40)->nullable()->unique()->after('payment_method');
            $table->string('payment_sender_number', 20)->nullable()->after('payment_trx_id');
            $table->boolean('payment_verified')->default(false)->after('payment_sender_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['payment_trx_id']);
            $table->dropColumn(['payment_trx_id', 'payment_sender_number', 'payment_verified']);
        });
    }
};
