<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');   // who created ride (driver)
            $table->unsignedBigInteger('vehicle_id'); // which vehicle is used
            $table->string('pickup_location');
            $table->string('destination');
            $table->integer('number_of_seats');
            $table->decimal('price', 10, 2);
            $table->date('ride_date');
            $table->time('ride_time');
            $table->boolean('accept_parcel')->default(false); // flag for parcel
            $table->json('services')->nullable(); // e.g. wifi, ac

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
