<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE courier_requests 
            MODIFY status ENUM(
                'pending',
                'accepted',
                'picked_up',
                'in_transit',
                'completed',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE courier_requests 
            MODIFY status ENUM(
                'pending',
                'accepted',
                'in_transit',
                'completed',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
