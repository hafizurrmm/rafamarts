<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->json('content')->nullable();           // Page Builder ব্লকগুলো
            $table->boolean('show_title')->default(true);  // ল্যান্ডিং পেজে টাইটেল লুকাতে চাইলে বন্ধ করুন
            $table->boolean('is_published')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
