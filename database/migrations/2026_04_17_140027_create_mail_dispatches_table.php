<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('mail_type');
            $table->string('unique_key');
            $table->string('subject');
            $table->string('context_hash', 64)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['recipient_email', 'mail_type', 'unique_key'], 'mail_dispatches_dedupe_unique');
            $table->index(['mail_type', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_dispatches');
    }
};
