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

        // Ensure FK/unique are added only if they don't already exist (idempotent for re-runs).
        $dbName = config('database.connections.mysql.database');
        $dealIdFkExists = DB::table('information_schema.key_column_usage')
            ->where('table_schema', $dbName)
            ->where('table_name', 'wishlists')
            ->where('column_name', 'deal_id')
            ->where('referenced_table_name', 'deals')
            ->whereNotNull('constraint_name')
            ->exists();

        $expectedUniqueIndexName = 'wishlists_user_id_deal_id_unique';
        $uniqueIndexExists = DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', 'wishlists')
            ->where('index_name', $expectedUniqueIndexName)
            ->exists();

        // Fallback heuristic: detect any unique index that covers both columns.
        if (! $uniqueIndexExists) {
            $uniqueIndexExists = DB::table('information_schema.statistics')
                ->select('index_name', 'column_name')
                ->where('table_schema', $dbName)
                ->where('table_name', 'wishlists')
                ->where('non_unique', 0)
                ->whereIn('column_name', ['user_id', 'deal_id'])
                ->get()
                ->groupBy('index_name')
                ->filter(function ($rows) {
                    $cols = $rows->pluck('column_name')->unique()->values();
                    return $cols->count() === 2 && $cols->contains('user_id') && $cols->contains('deal_id');
                })
                ->isNotEmpty();
        }

        if (! $dealIdFkExists || ! $uniqueIndexExists) {
            Schema::table('wishlists', function (Blueprint $table) use ($dealIdFkExists, $uniqueIndexExists) {
                if (! $dealIdFkExists) {
                    $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
                }
                if (! $uniqueIndexExists) {
                    $table->unique(['user_id', 'deal_id']);
                }
            });
        }

        // Make this step idempotent:
        // MySQL in this environment may not support `DROP ... IF EXISTS`, so we check the column/FK first.
        $dbName = config('database.connections.mysql.database');
        $columnExists = DB::table('information_schema.columns')
            ->where('table_schema', $dbName)
            ->where('table_name', 'wishlists')
            ->where('column_name', 'deal_offer_type_id')
            ->exists();

        if ($columnExists) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop only real FOREIGN KEY constraints (not unique indexes).
            // We join with TABLE_CONSTRAINTS to ensure constraint_type='FOREIGN KEY'.
            $fkNames = DB::table('information_schema.key_column_usage as kcu')
                ->join('information_schema.table_constraints as tc', function ($join) {
                    $join->on('tc.constraint_name', '=', 'kcu.constraint_name');
                })
                ->selectRaw('kcu.constraint_name as constraint_name')
                ->where('kcu.table_schema', $dbName)
                ->where('kcu.table_name', 'wishlists')
                ->where('kcu.column_name', 'deal_offer_type_id')
                ->where('tc.constraint_type', 'FOREIGN KEY')
                ->whereNotNull('kcu.constraint_name')
                ->pluck('constraint_name')
                ->values()
                ->all();

            foreach ($fkNames as $fkName) {
                try {
                    DB::statement("ALTER TABLE wishlists DROP FOREIGN KEY `$fkName`");
                } catch (\Throwable $e) {
                    // In case the schema is partially migrated and FK already gone.
                }
            }
            // Column might already be gone in partially migrated environments.
            try {
                DB::statement('ALTER TABLE wishlists DROP COLUMN deal_offer_type_id');
            } catch (\Throwable $e) {
                // ignore
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
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
