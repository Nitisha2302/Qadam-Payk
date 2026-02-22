<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_views', function (Blueprint $table) {
            $table->id();

            // Which story was viewed
            $table->foreignId('story_id')
                ->constrained('stories')
                ->onDelete('cascade');

            // Who viewed the story
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // When viewed
            $table->timestamps();

            // Prevent same user viewing same story multiple times
            $table->unique(['story_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_views');
    }
};
