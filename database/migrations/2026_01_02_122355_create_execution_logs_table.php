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
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')
                ->constrained('executions')
                ->cascadeOnDelete();
            
            $table->string('node_id')->nullable(); // xyflow node id
            $table->string('level')->default('info'); // info, error, warning
            $table->text('message');
            $table->json('data')->nullable(); // Contextual data
            
            $table->timestamps();
            
            $table->index('execution_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_logs');
    }
};
