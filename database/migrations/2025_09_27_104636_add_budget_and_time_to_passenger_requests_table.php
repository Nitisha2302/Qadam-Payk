<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passenger_requests', function (Blueprint $table) {
            $table->decimal('budget', 10, 2)->nullable()->after('parcel_images')->comment('Budget for the ride/parcel');
            $table->time('preferred_time')->nullable()->after('budget')->comment('Preferred time for the ride/parcel');
        });
    }

    public function down(): void
    {
        Schema::table('passenger_requests', function (Blueprint $table) {
            $table->dropColumn(['budget', 'preferred_time']);
        });
    }
};
