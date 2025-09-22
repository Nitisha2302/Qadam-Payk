<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->string('model_name'); // Car model e.g., City, Corolla
            $table->string('brand')->nullable(); // Car brand e.g., Honda, Toyota
            $table->string('color')->nullable(); // Car color e.g., Red, White
            $table->json('features')->nullable(); // Extra features in JSON (wifi, ac, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
