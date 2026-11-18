<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->index();
            $table->text('customer_address')->nullable();
            $table->string('delivery_zone', 30)->nullable();
            $table->json('cart');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('customer_ip', 45)->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_checkouts');
    }
};
