<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courier_live_trackings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('courier_request_id');
            $table->unsignedBigInteger('driver_id');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->timestamp('tracked_at')->nullable();

            $table->timestamps();

            $table->foreign('courier_request_id')
                ->references('id')
                ->on('courier_requests')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('courier_live_trackings');
    }
};