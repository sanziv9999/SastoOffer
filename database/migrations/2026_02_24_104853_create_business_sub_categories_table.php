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
        Schema::create('business_sub_categories', function (Blueprint $table) {
            $table->id();
            
            // Foreign key → belongs to one BusinessType
            $table->foreignId('primary_category_id')
                  ->constrained('primary_categories')
                  ->cascadeOnDelete();

            $table->string('name');                     // "Mobile Phones", "Laptops", "Plumbing", "Tuition Classes"
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('primary_category_id');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_sub_categories');
    }
};
