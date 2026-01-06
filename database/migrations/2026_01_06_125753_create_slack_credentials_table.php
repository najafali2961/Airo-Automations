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
        Schema::create('slack_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('team_id')->nullable();
            $table->string('team_name')->nullable();
            $table->string('access_token'); // OAuth Access Token
            $table->string('refresh_token')->nullable(); // Optional, depending on Slack App config
            $table->string('channel_id')->nullable(); // Default channel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_credentials');
    }
};
