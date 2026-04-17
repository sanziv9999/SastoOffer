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
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            // Foreign key + type for polymorphic
            $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();
            
            // Customer-specific fields
            $table->string('full_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable()->index();

            // foreign key to address table
            $table->foreignId('default_address_id')
                    ->nullable()
                    ->constrained('addresses')
                    ->nullOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
