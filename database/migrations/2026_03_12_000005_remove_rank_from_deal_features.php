<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('deal_features', 'rank')) {
            return;
        }

        // Drop any indexes/unique constraints involving rank.
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

        Schema::table('deal_features', function (Blueprint $table) {
            $table->dropColumn('rank');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('deal_features', 'rank')) {
            return;
        }

        Schema::table('deal_features', function (Blueprint $table) {
            $table->unsignedInteger('rank')->nullable()->after('is_new_arrival');
            $table->index('rank');
        });
    }
};

