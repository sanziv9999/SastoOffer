<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill deal_features from deals.is_featured (if column exists).
        if (Schema::hasColumn('deals', 'is_featured')) {
            $rows = DB::table('deals')->select('id', 'is_featured')->get();
            foreach ($rows as $row) {
                DB::table('deal_features')->updateOrInsert(
                    ['deal_id' => $row->id],
                    ['is_featured' => (bool) $row->is_featured, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            Schema::table('deals', function (Blueprint $table) {
                // drop index if exists
                try {
                    $table->dropIndex(['is_featured']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('is_featured');
            });
        }
    }

    public function down(): void
    {
        // Restore deals.is_featured and backfill from deal_features
        if (! Schema::hasColumn('deals', 'is_featured')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->boolean('is_featured')->default(false);
                $table->index('is_featured');
            });
        }

        if (Schema::hasTable('deal_features')) {
            $rows = DB::table('deal_features')->select('deal_id', 'is_featured')->get();
            foreach ($rows as $row) {
                DB::table('deals')->where('id', $row->deal_id)->update(['is_featured' => (bool) $row->is_featured]);
            }
        }
    }
};

