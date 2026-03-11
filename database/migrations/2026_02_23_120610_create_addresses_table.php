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
    $table->string('province');
    $table->string('district');
    $table->string('municipality');
    $table->string('ward_no');
    $table->string('tole');
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();
    $table->boolean('is_default')->default(true);
    $table->enum('label', [
        'Home', 'Office', 'Work', 'Pickup Point', 'Friend/Family', 'Other', 'Warehouse'
    ])->nullable();
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
