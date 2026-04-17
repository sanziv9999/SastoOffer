<?php

use App\Models\DisplayType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensures the `featured` row exists (e.g. DB restored without seeds, or row removed).
     */
    public function up(): void
    {
        if (! Schema::hasTable('display_types')) {
            return;
        }

        DisplayType::featured();
    }

    public function down(): void
    {
        // Do not delete: other code depends on this row.
    }
};
