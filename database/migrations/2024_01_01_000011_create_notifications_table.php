<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Filament ড্যাশবোর্ডের 🔔 নোটিফিকেশনের জন্য Laravel-এর স্ট্যান্ডার্ড notifications টেবিল।
// (আপনি আগে থেকেই `php artisan notifications:table` চালিয়ে থাকলে এই মাইগ্রেশন স্কিপ হবে।)
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('notifications')) {
            return;
        }

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
