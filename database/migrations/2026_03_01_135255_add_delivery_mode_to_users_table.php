<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            // courier type
            $table->enum('delivery_mode', ['walk','vehicle'])
                  ->nullable()
                  ->after('is_online');

            // walking courier gov id
            $table->longText('walking_gov_id')
                  ->nullable()
                  ->after('courier_selfie');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_mode',
                'walking_gov_id'
            ]);
        });
    }
};