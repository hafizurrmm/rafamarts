<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pure performance migration — no new columns, just indexes on columns that are
 * already filtered/sorted on heavily (homepage queries, admin search/filters,
 * fraud checks). Safe to run on an existing database with data in it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // HomeController filters by is_active (+ is_flash_sale for Flash Sale) on
            // every homepage/category/search request — composite index covers both.
            $table->index(['is_active', 'is_flash_sale'], 'products_active_flash_idx');
            $table->index('category_id', 'products_category_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Fraud checks (Blacklist, "3 orders/hour" auto-block) and admin search
            // both filter orders by phone number very often.
            $table->index('customer_phone', 'orders_customer_phone_idx');
            $table->index('status', 'orders_status_idx');
            $table->index('created_at', 'orders_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id', 'order_items_product_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_flash_idx');
            $table->dropIndex('products_category_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_customer_phone_idx');
            $table->dropIndex('orders_status_idx');
            $table->dropIndex('orders_created_at_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_product_idx');
        });
    }
};
