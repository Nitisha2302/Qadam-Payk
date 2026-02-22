<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       // who blocked
            $table->unsignedBigInteger('blocked_user_id'); // who got blocked
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_id', 'blocked_user_id']); // prevent duplicate blocks
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};
