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
       Schema::create('courier_request_cancellations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('courier_request_id');
            $table->unsignedBigInteger('cancelled_by_user_id');

            $table->enum('cancelled_by', ['user', 'driver']);

            $table->string('reason')->nullable();

            $table->timestamps();

            $table->foreign('courier_request_id')->references('id')->on('courier_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_request_cancellations');
    }
};
