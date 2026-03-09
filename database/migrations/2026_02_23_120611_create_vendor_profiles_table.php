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
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                    ->unique()
                    ->constrained()
                    ->cascadeOnDelete();
        
            // Vendor-specific fields
            $table->string('business_name', 150);
            $table->string('slug', 180)->unique();
            // Business category/type
            $table->foreignId('business_type_id')
                  ->nullable()
                  ->constrained('business_types')
                  ->nullOnDelete();
        
            $table->string('pan_number', 50)->unique()->nullable(); // Nepal PAN
            
            // Verification workflow
            $table->enum('verified_status', [
                'pending',
                'verified',
                'rejected',
                'suspended'
            ])->default('pending');

            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Business details
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();

            // Default location (pickup / main shop location)
            $table->foreignId('default_location_id')
                  ->nullable()
                  ->constrained('addresses')
                  ->nullOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index('verified_status');
            $table->index('business_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['business_type_id']);
            $table->dropForeign(['verified_by_user_id']);
            $table->dropForeign(['default_location_id']);
        });

        Schema::dropIfExists('vendor_profiles');
    }
};
