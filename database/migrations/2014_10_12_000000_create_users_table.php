<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('image')->nullable();
        $table->string('name')->nullable();
        $table->string('phone_number')->unique()->nullable();
        $table->string('email')->unique()->nullable();
         $table->string('password')->nullable();
        $table->string('role')->nullable();
        $table->string('otp')->nullable();
        $table->timestamp('otp_sent_at')->nullable();
        $table->boolean('email_verified')->default(false);
        $table->date('dob')->nullable();
        $table->enum('gender', ['male', 'female', 'other'])->nullable();
        $table->string('apple_token')->nullable();
        $table->string('facebook_token')->nullable();
        $table->string('google_token')->nullable();
        $table->boolean('is_social')->default(false);
        $table->string('device_type')->nullable();
        $table->string('device_token')->nullable();
        $table->string('api_token')->nullable();
        $table->string('vehicle_number')->nullable();
        $table->string('vehicle_type')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
