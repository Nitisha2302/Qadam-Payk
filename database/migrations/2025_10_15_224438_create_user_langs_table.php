<?php

// database/migrations/xxxx_xx_xx_create_user_langs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_langs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable();
            $table->string('language', 5)->default('ru'); // default Russian
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_langs');
    }
};
