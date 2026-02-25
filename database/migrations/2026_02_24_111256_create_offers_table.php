<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // Vendor / Merchant
            $table->foreignId('vendor_id')
                  ->constrained('vendor_profiles')
                  ->cascadeOnDelete();

            // Business Sub Category
            $table->foreignId('business_sub_category_id')
                  ->constrained('business_sub_categories')
                  ->cascadeOnDelete();

            $table->string('title', 255);
            $table->string('slug', 300)->unique();

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->json('highlights')->nullable();  // array of bullet points

            // Offer Type (FK to offer_types table)
            $table->foreignId('offer_type_id')
                  ->constrained('offer_types')
                  ->cascadeOnDelete();

            $table->enum('status', ['draft', 'active', 'inactive', 'expired'])->default('draft');

            $table->decimal('original_price', 15, 2)->nullable();
            $table->decimal('offer_price', 15, 2)->nullable();  // final/deal price
            $table->decimal('discount_percent', 5, 2)->nullable();

            $table->char('currency_code', 3)->default('NPR');

            $table->integer('total_inventory')->nullable();
            $table->integer('min_per_customer')->default(1);
            $table->integer('max_per_customer')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->integer('voucher_valid_days')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            // Custom per-offer validation rules
            $table->json('offer_validation_rules')->nullable();  // JSON for custom rules, e.g. {"discount_percent": "between:10,50"}

            $table->timestamps();

            // Indexes for performance
            $table->index(['vendor_id', 'status']);
            $table->index('offer_type_id');
            $table->index('starts_at');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};