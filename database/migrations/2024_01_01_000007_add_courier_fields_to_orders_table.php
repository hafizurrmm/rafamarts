<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_name')->nullable()->after('payment_method'); // steadfast | pathao
            $table->string('consignment_id')->nullable()->after('courier_name'); // steadfast id
            $table->string('tracking_code')->nullable()->after('consignment_id'); // steadfast/pathao tracking code
            $table->string('courier_order_id')->nullable()->after('tracking_code'); // pathao order id / consignment_id
            $table->string('courier_status')->nullable()->after('courier_order_id');
            $table->text('courier_response')->nullable()->after('courier_status'); // raw API response for debugging
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'courier_name', 'consignment_id', 'tracking_code',
                'courier_order_id', 'courier_status', 'courier_response',
            ]);
        });
    }
};
