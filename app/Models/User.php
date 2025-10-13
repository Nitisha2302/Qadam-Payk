<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;


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
        'user_lang',
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
        'is_blocked', 
    ];

    public function rides()
    {
        // A driver can have many rides they created
        return $this->hasMany(Ride::class, 'user_id');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'user_id');
    }

    public function rideBookings()
    {
        // A passenger can have many bookings
        return $this->hasMany(RideBooking::class, 'user_id');
    }

        // ✅ Accessor to format DOB
    public function getDobAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    

}
