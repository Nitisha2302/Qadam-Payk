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
        Schema::table('users', function (Blueprint $table) {
            // Add is_phone_verify after phone_number
            $table->boolean('is_phone_verify')
                ->default(false)
                ->after('phone_number');

            // Add government_id after gender (for file uploads like Aadhaar card)
            $table->string('government_id')
                ->nullable()
                ->after('gender');

            // Add id_verified (driver ID verification status)
            $table->boolean('id_verified')
                ->default(false)
                ->after('government_id');

            $table->string('device_id')
                ->nullable()
                ->after('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_phone_verify', 'government_id', 'id_verified']);
        });
    }
};
