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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_lang', 10)->nullable()->after('role')->comment('Preferred language of user');
        });
    }

    public function down()
    {
        Schema::table('users', callback: function (Blueprint $table) {
            $table->dropColumn('user_lang');
        });
    }

};
