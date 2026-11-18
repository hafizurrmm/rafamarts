<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Guest checkout fields (as in the screenshot: name, phone, address, note)
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->text('customer_note')->nullable();

            $table->decimal('sub_total', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);

            // 150 / 100 / 80 style delivery zones from the screenshot
            $table->enum('delivery_zone', ['inside_dhaka', 'near_dhaka', 'outside_dhaka'])->default('outside_dhaka');

            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->string('payment_method')->default('cod'); // cash on delivery by default

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
