<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('display_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('deal_offer_display', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_offer_type_id')->constrained('deal_offer_type')->cascadeOnDelete();
            // Column name kept as requested: display_as stores display_types.id
            $table->unsignedBigInteger('display_as');
            $table->timestamps();

            $table->foreign('display_as')->references('id')->on('display_types')->cascadeOnDelete();
            $table->unique(['deal_offer_type_id', 'display_as'], 'deal_offer_display_unique');
            $table->index('display_as');
        });

        $defaults = [
            'featured',
            'deals_of_the_day',
            'hot_sell',
            'new_arrival',
        ];

        foreach ($defaults as $name) {
            DB::table('display_types')->updateOrInsert(
                ['name' => $name],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Backfill from legacy deal_offer_type.display_as enum if present.
        if (Schema::hasColumn('deal_offer_type', 'display_as')) {
            $rows = DB::table('deal_offer_type')
                ->whereNotNull('display_as')
                ->select('id', 'display_as')
                ->get();

            foreach ($rows as $row) {
                $displayTypeId = DB::table('display_types')
                    ->where('name', $row->display_as)
                    ->value('id');

                if (! $displayTypeId) {
                    continue;
                }

                DB::table('deal_offer_display')->updateOrInsert(
                    [
                        'deal_offer_type_id' => $row->id,
                        'display_as' => $displayTypeId,
                    ],
                    [
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            Schema::table('deal_offer_type', function (Blueprint $table) {
                $table->dropIndex(['display_as']);
                $table->dropColumn('display_as');
            });
        }
    }

    public function down(): void
    {
        // Restore legacy enum on deal_offer_type for rollback compatibility.
        if (! Schema::hasColumn('deal_offer_type', 'display_as')) {
            Schema::table('deal_offer_type', function (Blueprint $table) {
                $table->enum('display_as', [
                    'featured',
                    'deals_of_the_day',
                    'hot_sell',
                    'new_arrival',
                ])->nullable()->after('status');
                $table->index('display_as');
            });
        }

        // Backfill back into legacy column.
        $rows = DB::table('deal_offer_display as dod')
            ->join('display_types as dt', 'dt.id', '=', 'dod.display_as')
            ->select('dod.deal_offer_type_id', 'dt.name')
            ->orderBy('dod.id')
            ->get();

        foreach ($rows as $row) {
            DB::table('deal_offer_type')
                ->where('id', $row->deal_offer_type_id)
                ->update(['display_as' => $row->name, 'updated_at' => now()]);
        }

        Schema::dropIfExists('deal_offer_display');
        Schema::dropIfExists('display_types');
    }
};

