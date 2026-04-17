<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Child offers only use the "featured" display tag; remove legacy tags from the pivot.
     */
    public function up(): void
    {
        $featuredId = DB::table('display_types')->where('name', 'featured')->value('id');
        if (! $featuredId) {
            return;
        }

        DB::table('deal_offer_display')
            ->where('display_as', '!=', (int) $featuredId)
            ->delete();
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
