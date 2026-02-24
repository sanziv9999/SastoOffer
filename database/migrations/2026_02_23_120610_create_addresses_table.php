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
    Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('address_line');
    $table->string('city');
    $table->string('state_province')->nullable();
    $table->string('postal_code')->nullable();
    $table->char('country_code', 2); // 'NP'
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->string('timezone')->nullable(); // 'Asia/Kathmandu'
    $table->boolean('is_default')->default(true);
    $table->enum('label', [
        'Home', 'Office', 'Work', 'Pickup Point', 'Friend/Family', 'Other', 'Warehouse'
    ])->nullable()->change();
    $table->timestamps();

    $table->index('user_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
