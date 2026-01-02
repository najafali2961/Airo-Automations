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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')
                ->constrained('flows')
                ->cascadeOnDelete();
            
            $table->string('type'); // trigger | condition | action
            $table->json('settings');
            $table->string('label')->nullable();
            
            $table->integer('position_x')->nullable();
            $table->integer('position_y')->nullable();
            
            $table->timestamps();
            
            $table->index('flow_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
