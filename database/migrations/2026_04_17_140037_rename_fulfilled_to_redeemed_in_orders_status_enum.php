<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','paid','fulfilled','redeemed','cancelled','refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("UPDATE orders SET status = 'redeemed' WHERE status = 'fulfilled'");
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','paid','redeemed','cancelled','refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','paid','fulfilled','redeemed','cancelled','refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("UPDATE orders SET status = 'fulfilled' WHERE status = 'redeemed'");
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','paid','fulfilled','cancelled','refunded') NOT NULL DEFAULT 'pending'");
    }
};

