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
        Schema::create('n8n_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('webhook_log_id')->nullable();
            $table->string('n8n_execution_id')->nullable();
            $table->string('status')->default('pending');
            $table->json('logs')->nullable();
            $table->timestamps();

            $table->index('shop_id');
            $table->foreign('webhook_log_id')->references('id')->on('webhook_logs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_executions');
    }
};
