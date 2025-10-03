<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Primary key for each message
            $table->unsignedBigInteger('conversation_id'); // Which conversation this message belongs to
            $table->unsignedBigInteger('sender_id'); // Sender's user ID
            $table->text('message')->nullable(); // Text content of message
            $table->string('type')->default('text'); // Type of message (text, image, file, system)
            $table->json('meta')->nullable(); // Extra data for files, images, etc.
            $table->timestamp('send_at')->nullable(); // When the receiver read this message
            $table->timestamp('read_at')->nullable(); // When the receiver read this message
          
            $table->timestamps(); // created_at = message timestamp, updated_at

            // Foreign keys
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('messages');
    }
};
