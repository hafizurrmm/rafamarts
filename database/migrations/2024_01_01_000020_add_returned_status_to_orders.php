<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// অর্ডার স্ট্যাটাসে নতুন "Returned" অপশন যোগ করা (MySQL/MariaDB enum কলাম বদলাতে হয়)।
return new class extends Migration {
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','on_hold','completed','cancelled','returned') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("UPDATE orders SET status='cancelled' WHERE status='returned'");
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','on_hold','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
