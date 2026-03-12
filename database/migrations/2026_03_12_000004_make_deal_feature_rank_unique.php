<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Treat rank=0 as "unranked" and allow multiple unranked deals by converting to NULL.
        if (Schema::hasColumn('deal_features', 'rank')) {
            DB::table('deal_features')->where('rank', 0)->update(['rank' => null]);
        }

        // Make rank nullable (avoid doctrine/dbal by using raw SQL).
        DB::statement('ALTER TABLE `deal_features` MODIFY `rank` INT UNSIGNED NULL');

        // Normalize any existing ranked rows so ranks are unique before adding constraint.
        // (Some rows may already have duplicate ranks.)
        $ranked = DB::table('deal_features')
            ->select('id')
            ->whereNotNull('rank')
            ->orderBy('rank')
            ->orderBy('id')
            ->get();

        $i = 1;
        foreach ($ranked as $row) {
            DB::table('deal_features')->where('id', $row->id)->update(['rank' => $i]);
            $i++;
        }

        // Drop any existing index on rank, then add unique constraint.
        $indexes = DB::select(
            "SELECT DISTINCT index_name FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = 'deal_features' AND column_name = 'rank'"
        );
        foreach ($indexes as $idx) {
            $name = $idx->index_name ?? null;
            if (! $name || $name === 'PRIMARY') {
                continue;
            }
            DB::statement("ALTER TABLE `deal_features` DROP INDEX `{$name}`");
        }

        DB::statement('ALTER TABLE `deal_features` ADD UNIQUE `deal_features_rank_unique` (`rank`)');
    }

    public function down(): void
    {
        Schema::table('deal_features', function (Blueprint $table) {
            try {
                $table->dropUnique(['rank']);
            } catch (\Throwable $e) {
                // ignore if missing
            }
        });

        DB::statement('ALTER TABLE `deal_features` MODIFY `rank` INT UNSIGNED NOT NULL DEFAULT 0');
        DB::table('deal_features')->whereNull('rank')->update(['rank' => 0]);

        Schema::table('deal_features', function (Blueprint $table) {
            try {
                $table->index('rank');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};

