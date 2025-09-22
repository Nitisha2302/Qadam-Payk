<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'image',
        'name',
        'phone_number',
        'is_phone_verify',
        'email',
        'role',
        'otp',
        'otp_sent_at',
        'email_verified',
        'dob',
        'gender',
        'government_id',
        'id_verified',
         'password',
        'apple_token',
        'facebook_token',
        'google_token',
        'is_social',
        'device_type',
        'device_id',
        'device_token',
        'api_token',
        'vehicle_number', // only for drivers
        'vehicle_type',   // car, bike, van (for parcel)
    ];

}
