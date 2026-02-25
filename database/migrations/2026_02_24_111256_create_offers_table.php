<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            // Vendor (FK to vendor_profiles.id)
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
            $table->json('highlights')->nullable(); // array of bullet points

            // Offer Type
            $table->foreignId('offer_type_id')
                  ->constrained('offer_types')
                  ->cascadeOnDelete();

            $table->enum('status', ['draft', 'active', 'inactive', 'expired'])->default('draft');

            // Price & Discount Fields (all nullable except original_price)
            $table->decimal('original_price', 15, 2);           // Required: MRP / original price
            $table->decimal('offer_price', 15, 2)->nullable();  // Final price customer pays
            $table->decimal('discount_percent', 5, 2)->nullable(); // % discount
            $table->decimal('discount_amount', 15, 2)->nullable(); // Fixed Rs amount off
            $table->decimal('savings_amount', 15, 2)->nullable();  // original_price - offer_price
            $table->decimal('savings_percent', 5, 2)->nullable();  // (savings_amount / original_price) * 100

            $table->char('currency_code', 3)->default('NPR');

            // Inventory & Limits
            $table->integer('total_inventory')->nullable()->comment('NULL = unlimited');
            $table->integer('min_per_customer')->default(1);
            $table->integer('max_per_customer')->nullable();

            // Dates & Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('voucher_valid_days')->nullable()->comment('Days voucher is valid after redemption');

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            // Custom per-offer validation rules (JSON)
            $table->json('offer_validation_rules')->nullable();

            $table->timestamps();

            // Soft deletes (optional – good for archiving expired offers)
            $table->softDeletes();

            // Indexes for performance
            $table->index(['vendor_id', 'status']);
            $table->index('offer_type_id');
            $table->index('business_sub_category_id');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('is_featured');
            $table->index('slug'); // for fast slug lookups
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};