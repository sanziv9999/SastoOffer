<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            if (! Schema::hasColumn('wishlists', 'deal_id')) {
                $table->unsignedBigInteger('deal_id')->nullable()->after('user_id');
            }
        });

        if (Schema::hasColumn('wishlists', 'deal_offer_type_id')) {
            DB::statement('
                UPDATE wishlists w
                INNER JOIN deal_offer_type dot ON dot.id = w.deal_offer_type_id
                SET w.deal_id = dot.deal_id
                WHERE w.deal_id IS NULL
            ');
        }

        DB::table('wishlists')
            ->selectRaw('MAX(id) as keep_id, user_id, deal_id')
            ->whereNotNull('deal_id')
            ->groupBy('user_id', 'deal_id')
            ->get()
            ->each(function ($row) {
                DB::table('wishlists')
                    ->where('user_id', $row->user_id)
                    ->where('deal_id', $row->deal_id)
                    ->where('id', '!=', $row->keep_id)
                    ->delete();
            });

        Schema::table('wishlists', function (Blueprint $table) {
            if (Schema::hasColumn('wishlists', 'deal_id')) {
                $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
                $table->unique(['user_id', 'deal_id']);
            }
        });

        Schema::table('wishlists', function (Blueprint $table) {
            if (Schema::hasColumn('wishlists', 'deal_offer_type_id')) {
                $table->dropConstrainedForeignId('deal_offer_type_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            if (! Schema::hasColumn('wishlists', 'deal_offer_type_id')) {
                $table->unsignedBigInteger('deal_offer_type_id')->nullable()->after('user_id');
            }
        });

        if (Schema::hasColumn('wishlists', 'deal_id')) {
            DB::statement('
                UPDATE wishlists w
                INNER JOIN (
                    SELECT deal_id, MIN(id) as deal_offer_type_id
                    FROM deal_offer_type
                    GROUP BY deal_id
                ) mapped ON mapped.deal_id = w.deal_id
                SET w.deal_offer_type_id = mapped.deal_offer_type_id
                WHERE w.deal_offer_type_id IS NULL
            ');
        }

        DB::table('wishlists')
            ->selectRaw('MAX(id) as keep_id, user_id, deal_offer_type_id')
            ->whereNotNull('deal_offer_type_id')
            ->groupBy('user_id', 'deal_offer_type_id')
            ->get()
            ->each(function ($row) {
                DB::table('wishlists')
                    ->where('user_id', $row->user_id)
                    ->where('deal_offer_type_id', $row->deal_offer_type_id)
                    ->where('id', '!=', $row->keep_id)
                    ->delete();
            });

        Schema::table('wishlists', function (Blueprint $table) {
            if (Schema::hasColumn('wishlists', 'deal_offer_type_id')) {
                $table->foreign('deal_offer_type_id')->references('id')->on('deal_offer_type')->cascadeOnDelete();
                $table->unique(['user_id', 'deal_offer_type_id']);
            }
        });

        Schema::table('wishlists', function (Blueprint $table) {
            if (Schema::hasColumn('wishlists', 'deal_id')) {
                $table->dropConstrainedForeignId('deal_id');
            }
        });
    }
};
