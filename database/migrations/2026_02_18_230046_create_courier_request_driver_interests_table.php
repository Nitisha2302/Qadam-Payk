<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('courier_request_driver_interests', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('courier_request_id')
                ->comment('Courier request id');

            $table->unsignedBigInteger('driver_id')
                ->comment('Driver user id who showed interest');

            $table->decimal('driver_price', 10, 2)
                ->nullable()
                ->comment('Driver proposed price');

            $table->text('message')
                ->nullable()
                ->comment('Optional driver message');

            $table->timestamps();

            $table->foreign('courier_request_id')
                ->references('id')
                ->on('courier_requests')
                ->onDelete('cascade');

            $table->foreign('driver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // prevent duplicate interest
            $table->unique(['courier_request_id', 'driver_id'], 'courier_req_driver_unique');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_request_driver_interests');
    }
};
