<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // The user (driver/passenger)
            $table->string('title');
            $table->text('description')->nullable();
            $table->tinyInteger('notification_type')->default(0);
            $table->unsignedBigInteger('booking_id')->nullable()->index(); // Optional reference to a booking
            $table->timestamp('notification_created_at')->nullable();
            $table->timestamps();

            // Foreign key (optional)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // If you want booking relation
            $table->foreign('booking_id')->references('id')->on('ride_bookings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
