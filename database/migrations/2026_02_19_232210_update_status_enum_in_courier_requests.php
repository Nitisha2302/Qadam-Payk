<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        /*
        Status Flow Explanation:

        pending      = Waiting for courier (default status)
        accepted     = Driver has accepted the request
        in_transit   = Driver picked up parcel and is delivering
        completed    = Parcel successfully delivered
        cancelled    = Cancelled by sender or driver
        expired      = Auto expired after 30 minutes if no driver accepts
        */

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
            COMMENT 'pending=waiting for courier, accepted=driver accepted, in_transit=parcel picked & delivering, completed=delivered, cancelled=cancelled by user/driver, expired=auto expired after 30 mins'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE courier_requests 
            MODIFY status ENUM(
                'pending',
                'accepted',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'pending'
            COMMENT 'pending=waiting for courier, accepted=driver accepted, cancelled=cancelled, expired=auto expired'
        ");
    }
};
