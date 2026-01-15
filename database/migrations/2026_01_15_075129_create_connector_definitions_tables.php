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
        Schema::create('connector_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_id')->constrained('connectors')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('type')->default('trigger');
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->string('topic')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['connector_id', 'key']);
        });

        Schema::create('connector_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_id')->constrained('connectors')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->json('fields')->nullable();
            $table->string('topic')->nullable(); // Some actions might need this context
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['connector_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connector_actions');
        Schema::dropIfExists('connector_triggers');
    }
};
