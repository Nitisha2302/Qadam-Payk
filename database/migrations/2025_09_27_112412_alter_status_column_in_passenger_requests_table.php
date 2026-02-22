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
        Schema::table('passenger_requests', function (Blueprint $table) {
            // Change status column to string with default "pending"
            $table->string('status')
                  ->default('pending')
                  ->comment('Request status, default pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_requests', function (Blueprint $table) {
            // Rollback to enum if needed
            $table->enum('status', ['pending','accepted','confirmed'])
                  ->default('pending')
                  ->change();
        });
    }
};
