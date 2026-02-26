<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropForeign(['delivery_address_id']);
            $table->renameColumn('delivery_address_id', 'default_address_id');
            $table->foreign('default_address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropForeign(['default_address_id']);
            $table->renameColumn('default_address_id', 'delivery_address_id');
            $table->foreign('delivery_address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }
};
