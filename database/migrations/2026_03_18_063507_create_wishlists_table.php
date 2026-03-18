<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            // We reference the specific offer (pivot) to bookmark a specific deal + its price/details
            $blueprint->foreignId('deal_offer_type_id')->constrained('deal_offer_type')->cascadeOnDelete();
            $blueprint->timestamps();

            $blueprint->unique(['user_id', 'deal_offer_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
