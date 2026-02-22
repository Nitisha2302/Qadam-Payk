<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRideDateTimeToRideBookingsTable extends Migration
{
    public function up()
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
             $table->foreignId('request_id')->nullable()->after('ride_id')->constrained('passenger_requests')->onDelete('set null');
            $table->date('ride_date')->nullable()->after('status');
            $table->time('ride_time')->nullable()->after('ride_date');
        });
    }

    public function down()
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn(['ride_date', 'ride_time']);
        });
    }
}
