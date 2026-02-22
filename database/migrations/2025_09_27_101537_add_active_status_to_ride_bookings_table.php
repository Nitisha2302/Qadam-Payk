<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->string('active_status')
                  ->default(0)
                  ->after('status')
                  ->comment('0 = pending, 1 = active,2 = complete'); // Column to identify if booking is ride or parcel
        });
    }

    public function down(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn('active_status');
        });
    }
};


