<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courier_requests', function (Blueprint $table) {

            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('pickup_location');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');

            $table->decimal('drop_latitude', 10, 7)->nullable()->after('drop_location');
            $table->decimal('drop_longitude', 10, 7)->nullable()->after('drop_latitude');

        });
    }

    public function down(): void
    {
        Schema::table('courier_requests', function (Blueprint $table) {

            $table->dropColumn([
                'pickup_latitude',
                'pickup_longitude',
                'drop_latitude',
                'drop_longitude'
            ]);

        });
    }
};
