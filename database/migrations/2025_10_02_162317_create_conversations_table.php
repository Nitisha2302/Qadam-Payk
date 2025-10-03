<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id(); // Primary key for the conversation
            $table->unsignedBigInteger('user_one_id'); // One participant's user ID
            $table->unsignedBigInteger('user_two_id'); // Other participant's user ID
            $table->timestamp('last_message_at')->nullable(); // Last message timestamp (for ordering conversation list)
            $table->text('last_message_preview')->nullable(); // Short preview of last message
            $table->unsignedBigInteger('last_message_id')->nullable(); // ID of last message
            $table->timestamps(); // created_at / updated_at

            // Foreign keys linking to users table
            $table->foreign('user_one_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_two_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_one_id', 'user_two_id']); // Ensure only one conversation per user pair
        });
    }

    public function down() {
        Schema::dropIfExists('conversations');
    }
};

