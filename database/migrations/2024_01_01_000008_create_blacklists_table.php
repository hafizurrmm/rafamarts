<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('name')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedInteger('return_count')->default(0);
            $table->boolean('is_active')->default(true); // false = unblocked
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
