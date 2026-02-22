<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->tinyInteger('type')
                  ->default(0)
                  ->after('ride_id')
                  ->comment('0 = ride, 1 = parcel'); // Column to identify if booking is ride or parcel
        });
    }

    public function down(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};


