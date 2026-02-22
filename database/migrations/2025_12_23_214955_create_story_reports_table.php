<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('story_reports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('story_id');
            $table->unsignedBigInteger('user_id');
            $table->string('reason')->nullable();

            $table->timestamps();

            // Prevent same user from reporting same story multiple times
            $table->unique(['story_id', 'user_id']);

            // Foreign keys
            $table->foreign('story_id')
                  ->references('id')->on('stories')
                  ->onDelete('cascade');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_reports');
    }
};
