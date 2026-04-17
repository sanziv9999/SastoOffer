<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_offer_type', function (Blueprint $table) {
            if (! Schema::hasColumn('deal_offer_type', 'display_as')) {
                $table->enum('display_as', [
                    'featured',
                    'deals_of_the_day',
                    'hot_sell',
                    'new_arrival',
                ])->nullable()->after('status');
                $table->index('display_as');
            }
        });

        // Backfill from legacy deal_features table if it exists.
        if (Schema::hasTable('deal_features')) {
            $rows = DB::table('deal_features')->get();

            foreach ($rows as $row) {
                $displayAs = null;
                if (! empty($row->is_featured)) {
                    $displayAs = 'featured';
                } elseif (! empty($row->is_deal_of_day)) {
                    $displayAs = 'deals_of_the_day';
                } elseif (! empty($row->is_best_seller)) {
                    $displayAs = 'hot_sell';
                } elseif (! empty($row->is_new_arrival)) {
                    $displayAs = 'new_arrival';
                }

                if (! $displayAs) {
                    continue;
                }

                // Keep a single display_as marker per deal on the first active offer.
                $targetPivotId = DB::table('deal_offer_type')
                    ->where('deal_id', $row->deal_id)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->value('id');

                if (! $targetPivotId) {
                    $targetPivotId = DB::table('deal_offer_type')
                        ->where('deal_id', $row->deal_id)
                        ->orderBy('id')
                        ->value('id');
                }

                if (! $targetPivotId) {
                    continue;
                }

                DB::table('deal_offer_type')
                    ->where('deal_id', $row->deal_id)
                    ->update([
                        'display_as' => null,
                        'updated_at' => now(),
                    ]);

                DB::table('deal_offer_type')
                    ->where('id', $targetPivotId)
                    ->update([
                        'display_as' => $displayAs,
                        'updated_at' => now(),
                    ]);
            }

            Schema::dropIfExists('deal_features');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('deal_features')) {
            Schema::create('deal_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('deal_id')->unique()->constrained('deals')->cascadeOnDelete();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_deal_of_day')->default(false);
                $table->boolean('is_best_seller')->default(false);
                $table->boolean('is_new_arrival')->default(false);
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('deal_offer_type', 'display_as')) {
            Schema::table('deal_offer_type', function (Blueprint $table) {
                $table->dropIndex(['display_as']);
                $table->dropColumn('display_as');
            });
        }
    }
};

