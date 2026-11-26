<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            if (! Schema::hasColumn('products', 'cost_price')) {
                $t->decimal('cost_price', 10, 2)->nullable();
            }
        });
        Schema::table('order_items', function (Blueprint $t) {
            if (! Schema::hasColumn('order_items', 'cost_price')) {
                $t->decimal('cost_price', 10, 2)->nullable();
            }
        });
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $t) {
                $t->id();
                $t->date('spent_on')->index();
                $t->string('title');
                $t->string('type', 30)->default('other');
                $t->decimal('amount', 12, 2);
                $t->text('note')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn('cost_price'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('cost_price'));
    }
};
