<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('deal_offer_type_id')->nullable()->after('deal_id');
            $table->index('deal_offer_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['deal_offer_type_id']);
            $table->dropColumn('deal_offer_type_id');
        });
    }
};
