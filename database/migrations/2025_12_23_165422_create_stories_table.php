<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['photo', 'video']);
            $table->string('media'); // store path
            $table->string('route')->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->boolean('reported')->default(false);
            $table->timestamp('expires_at'); // for 24 hours auto-delete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
