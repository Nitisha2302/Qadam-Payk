<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['ride_id']);

            // Make the column nullable
            $table->unsignedBigInteger('ride_id')->nullable()->change();

            // Re-add foreign key with onDelete('cascade')
            $table->foreign('ride_id')->references('id')->on('rides')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropForeign(['ride_id']);
            $table->unsignedBigInteger('ride_id')->nullable(false)->change();
            $table->foreign('ride_id')->references('id')->on('rides')->onDelete('cascade');
        });
    }
};
