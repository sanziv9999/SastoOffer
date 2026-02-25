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
        Schema::create('deals', function (Blueprint $table) {
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

            $table->enum('status', ['draft', 'active', 'inactive', 'expired'])->default('draft');

            // Inventory & Limits
            $table->integer('total_inventory')->nullable()->comment('NULL = unlimited');
            $table->integer('min_per_customer')->default(1);
            $table->integer('max_per_customer')->nullable();

            // Dates & Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('voucher_valid_days')->nullable()
                ->comment('Days voucher is valid after redemption');

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            // Custom per-deal validation rules (JSON)
            $table->json('offer_validation_rules')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['vendor_id', 'status']);
            $table->index('business_sub_category_id');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('is_featured');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};