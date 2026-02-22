<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('courier_requests', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id')
                ->comment('Sender user id who created courier request');

            $table->string('pickup_location')->nullable()
                ->comment('Pickup address/location');

            $table->string('drop_location')->nullable()
                ->comment('Drop address/location');

            $table->string('distance')
                ->nullable()
                ->comment('Distance between pickup and drop (example: 12km)');

            $table->string('time')
                ->nullable()
                ->comment('Estimated time (example: 25min)');

            $table->enum('trip_type', ['incity','intercity'])
                ->default('incity')
                ->comment('Courier type: incity or intercity');

            $table->string('sender_name')->nullable()
                ->comment('Sender contact name');

            $table->string('sender_phone')->nullable()
                ->comment('Sender contact phone number');

            $table->string('sender_landmark')
                ->nullable()
                ->comment('Sender landmark optional');

            $table->string('receiver_name')->nullable()
                ->comment('Receiver contact name');

            $table->string('receiver_phone')->nullable()
                ->comment('Receiver contact phone number');

            $table->string('receiver_landmark')
                ->nullable()
                ->comment('Receiver landmark optional');

            $table->text('package_description')
                ->nullable()
                ->comment('Package details/description');

            $table->enum('package_size', ['small','medium','large'])
                ->comment('small <2kg, medium 2-10kg, large >10kg');

            $table->text('instruction')
                ->nullable()
                ->comment('Special instruction optional');

            $table->decimal('suggested_price', 10, 2)
                ->nullable()
                ->comment('Sender suggested price');

            $table->enum('payment_method', ['cash','card'])
                ->default('cash')
                ->comment('Payment method');

            $table->enum('paid_by', ['sender','receiver'])
                ->default('sender')
                ->comment('Who will pay sender or receiver');

            $table->enum('status', ['pending','accepted','cancelled','expired','completed'])
                ->default('pending')
                ->comment('pending=waiting for courier, accepted=driver accepted, expired=auto deleted after 30 mins');

            $table->unsignedBigInteger('accepted_driver_id')
                ->nullable()
                ->comment('Driver user id who accepted this request');

            $table->timestamp('expires_at')
                ->nullable()
                ->comment('Request expiry time, after this request will be deleted');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('accepted_driver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

   public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('courier_requests');
        Schema::enableForeignKeyConstraints();
    }

};
