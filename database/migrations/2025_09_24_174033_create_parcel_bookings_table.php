<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParcelBookingsTable extends Migration
{
    public function up()
    {
        Schema::create('parcel_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Pickup info
            $table->string('pickup_city');
            $table->string('pickup_name');
            $table->string('pickup_contact');

            // Drop info
            $table->string('drop_city');
            $table->string('drop_name');
            $table->string('drop_contact');

            $table->text('parcel_description')->nullable();
            $table->json('parcel_images')->nullable(); // multiple images
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parcel_bookings');
    }
}
