<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_offer_type', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('offer_type_id');

            $table->decimal('original_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable();
            $table->decimal('discount_percent', 8, 4)->nullable();
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('savings_amount', 12, 2)->nullable();
            $table->decimal('savings_percent', 8, 4)->nullable();

            $table->string('currency_code', 3)->default('NPR');
            $table->json('params')->nullable();
            $table->string('status')->default('active');

            $table->timestamps();

            // Foreign keys with **explicit names**
            $table->foreign('deal_id', 'deal_offer_type_deal_id_foreign')
                ->references('id')
                ->on('deals')
                ->onDelete('cascade');

            $table->foreign('offer_type_id', 'deal_offer_type_offer_type_id_foreign')
                ->references('id')
                ->on('offer_types')
                ->onDelete('cascade');

            // Indexes (optional but recommended)
            $table->index('deal_id', 'deal_offer_type_deal_id_index');
            $table->index('offer_type_id', 'deal_offer_type_offer_type_id_index');

            // Prevent duplicate combinations
            $table->unique(['deal_id', 'offer_type_id'], 'deal_offer_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_offer_type');
    }
};