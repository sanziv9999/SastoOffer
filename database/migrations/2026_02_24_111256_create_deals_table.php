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

            // Category (leaf category from unified categories table)
            // NOTE: FK added in a later migration once categories table exists
            $table->foreignId('category_id');

            $table->string('title', 255);
            $table->string('slug', 300)->unique();

            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->json('highlights')->nullable(); // array of bullet points

            $table->enum('status', ['draft', 'active', 'inactive', 'expired'])->default('draft');

            // Inventory & Limits
            $table->integer('total_inventory')->nullable()->comment('NULL = unlimited');
            // min/max per customer are managed per-offer (deal_offer_type)


            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            // offer validation rules are managed per-offer (deal_offer_type)

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['vendor_id', 'status']);
            $table->index('category_id');
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