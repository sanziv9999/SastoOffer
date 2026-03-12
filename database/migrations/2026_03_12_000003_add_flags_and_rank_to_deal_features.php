<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_features', function (Blueprint $table) {
            $table->boolean('is_deal_of_day')->default(false)->after('is_featured');
            $table->boolean('is_best_seller')->default(false)->after('is_deal_of_day');
            $table->boolean('is_new_arrival')->default(false)->after('is_best_seller');
            $table->unsignedInteger('rank')->default(0)->after('is_new_arrival');

            $table->index('is_deal_of_day');
            $table->index('is_best_seller');
            $table->index('is_new_arrival');
            $table->index('rank');
        });
    }

    public function down(): void
    {
        Schema::table('deal_features', function (Blueprint $table) {
            $table->dropIndex(['is_deal_of_day']);
            $table->dropIndex(['is_best_seller']);
            $table->dropIndex(['is_new_arrival']);
            $table->dropIndex(['rank']);

            $table->dropColumn(['is_deal_of_day', 'is_best_seller', 'is_new_arrival', 'rank']);
        });
    }
};

