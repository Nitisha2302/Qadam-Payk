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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('language_code', 10)->nullable()->after('country'); // e.g., 'en', 'ru', 'ar'
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('language_code');
        });
    }

};
