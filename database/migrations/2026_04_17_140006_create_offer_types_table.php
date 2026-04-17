<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique()
                ->comment('Internal unique code: percentage_discount, fixed_amount_discount, bogo, etc.');

            $table->string('display_name', 100)
                ->comment('User-friendly name shown to vendors/admins: Percentage Off, Flat Discount, Buy 1 Get 1');

            $table->string('slug', 120)->unique()->nullable()
                ->comment('URL-friendly slug if needed for frontend or docs');

            $table->text('description')->nullable()
                ->comment('Detailed explanation of how this offer type works');

            // ─── Rule fields ───────────────────────────────────────────────
            $table->json('calculation_rule')->nullable()
                ->comment('JSON structure defining how prices are calculated. Example: {"type": "percentage", "formula": "...", "display": "{discount_percent}% OFF"}');

            $table->json('required_params')->nullable()
                ->comment('Array of required input parameters when attaching this offer type to a deal. Example: ["discount_percent", "min_order_value"]');

            $table->json('default_values')->nullable()
                ->comment('Default parameter values if not provided. Example: {"discount_percent": 10, "min_order_value": 1000}');

            $table->boolean('is_active')->default(true)
                ->comment('Whether this offer type is available for use');

            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_types');
    }
};