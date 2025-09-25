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
        Schema::create('passenger_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Request type: ride or parcel
            $table->string('type')->nullable();

            // Ride fields
            $table->string('pickup_location')->nullable();
            $table->string('destination')->nullable();
            $table->integer('number_of_seats')->default(1);

            // Parcel fields
            $table->string('pickup_contact_name')->nullable();
            $table->string('pickup_contact_no')->nullable();
            $table->string('drop_contact_name')->nullable();
            $table->string('drop_contact_no')->nullable();
            $table->text('parcel_details')->nullable();
             $table->json('parcel_images')->nullable();

            // Common fields
            $table->date('ride_date')->nullable();
            $table->time('ride_time')->nullable();
            $table->json('services')->nullable();
            // New fields
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null'); // Driver who accepted
            $table->enum('status', ['pending','accepted','confirmed'])->default('pending'); // Request status

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passenger_requests');
    }
};
