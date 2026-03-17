<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deal_offer_type', function (Blueprint $table) {
            if (! Schema::hasColumn('deal_offer_type', 'starts_at')) {
                $table->timestamp('starts_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('deal_offer_type', 'ends_at')) {
                $table->timestamp('ends_at')->nullable()->after('starts_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deal_offer_type', function (Blueprint $table) {
            if (Schema::hasColumn('deal_offer_type', 'ends_at')) {
                $table->dropColumn('ends_at');
            }
            if (Schema::hasColumn('deal_offer_type', 'starts_at')) {
                $table->dropColumn('starts_at');
            }
        });
    }
};

