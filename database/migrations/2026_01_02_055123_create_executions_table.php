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
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')
                ->constrained('flows')
                ->cascadeOnDelete();
            
            $table->string('event');
            $table->string('external_event_id');
            $table->json('payload');
            
            $table->string('status'); // success | failed | partial | running
            $table->text('error_message')->nullable();
            
            $table->integer('nodes_executed')->default(0);
            $table->integer('actions_completed')->default(0);
            $table->integer('duration_ms')->nullable();
            
            $table->timestamps();
            
            $table->unique(['flow_id', 'external_event_id']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('executions');
    }
};
