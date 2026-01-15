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
        Schema::create('user_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('connector_slug')->index(); // e.g. 'google', 'slack'
            $table->boolean('is_active')->default(true);
            $table->longText('credentials')->nullable(); // Encrypted string (not JSON type in DB)
            $table->timestamp('expires_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            // Unique constraint: One active connection per service per user (optional, usually 1:1)
            $table->unique(['user_id', 'connector_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_connectors');
    }
};
