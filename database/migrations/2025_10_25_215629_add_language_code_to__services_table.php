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
        Schema::table('Services', function (Blueprint $table) {
                 $table->string('language_code', 10)->nullable()->after('service_image'); // e.g., 'en', 'ru', 'ar'

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Services', function (Blueprint $table) {
           $table->dropColumn('language_code');
        });
    }
};
