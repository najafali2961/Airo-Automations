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
        Schema::create('edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')
                ->constrained('flows')
                ->cascadeOnDelete();
            
            $table->foreignId('source_node_id')
                ->constrained('nodes')
                ->cascadeOnDelete();
            
            $table->foreignId('target_node_id')
                ->constrained('nodes')
                ->cascadeOnDelete();
            
            $table->string('label')->nullable(); // then | true | false | error
            
            $table->timestamps();
            
            $table->index(['flow_id', 'source_node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edges');
    }
};
