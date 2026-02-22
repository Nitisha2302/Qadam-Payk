<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // 0 = offline (sender mode), 1 = online (courier mode)
            $table->tinyInteger('is_online')
                ->default(0)
                ->comment('0 = offline, 1 = online courier mode')
                ->after('id_verified');

            // courier verification status
            $table->enum('courier_doc_status', ['not_submitted','pending','approved','rejected'])
                ->default('not_submitted')
                ->comment('Courier documents verification status')
                ->after('is_online');

            // store multiple passport images as json
            $table->longText('passport_images')
                ->nullable()
                ->comment('Multiple passport images stored as JSON array')
                ->after('courier_doc_status');

            // store multiple license images as json (nullable)
            $table->longText('license_images')
                ->nullable()
                ->comment('Multiple license images stored as JSON array (nullable)')
                ->after('passport_images');

            // selfie image for courier verification
            $table->string('courier_selfie')
                ->nullable()
                ->comment('Selfie image for courier identity verification')
                ->after('license_images');

            // admin rejection reason
            $table->text('courier_reject_reason')
                ->nullable()
                ->comment('Courier document rejection reason by admin')
                ->after('courier_selfie');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'is_online',
                'courier_doc_status',
                'passport_images',
                'license_images',
                'courier_selfie',
                'courier_reject_reason'
            ]);
        });
    }
};
