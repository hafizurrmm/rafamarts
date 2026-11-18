<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// অর্ডার স্ট্যাটাসে নতুন "On Hold" অপশন যোগ করা (MySQL/MariaDB enum কলাম বদলাতে হয়)।
return new class extends Migration {
    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','on_hold','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("UPDATE orders SET status='pending' WHERE status='on_hold'");
            DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
