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
        Schema::create('offer_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();              // internal code: 'percentage_discount', 'fixed_amount_discount', etc.
            $table->string('display_name', 100);                // what vendors/admins see: 'Percentage Off', 'Flat Rs Off'
            $table->string('slug', 120)->unique()->nullable();  // for frontend URLs if needed: 'percentage-off'
            $table->text('description')->nullable();            // explanation of what this offer type means
            


            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for faster lookups
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_types');
    }
};