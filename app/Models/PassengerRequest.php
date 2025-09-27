<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class PassengerRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'pickup_location',
        'destination',
        'number_of_seats',
        'pickup_contact_name',
        'pickup_contact_no',
        'drop_contact_name',
        'drop_contact_no',
        'parcel_details',
        'parcel_images',
        'ride_date',
        'ride_time',
        'services',
        'driver_id',
        'status',
        'budget',
        'preferred_time',
    ];

    protected $casts = [
        'services' => 'array',
         'type' => 'integer', // 0 = ride, 1 = parcel
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // ✅ Format ride_date as d-m-Y
    public function getRideDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->format('d-m-Y')
            : null;
    }

    // ✅ Format ride_time as H:i
    public function getRideTimeAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->format('H:i')
            : null;
    }

     // ✅ Accessor to get full service details
    public function getServicesDetailsAttribute()
    {
        return Service::whereIn('id', $this->services ?? [])
                      ->get(['id', 'service_name', 'service_image']);
    }

    // Driver relationship
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

   // Vehicle relationship (through the driver)
    public function vehicle()
    {
        return $this->hasOneThrough(
            Vehicle::class,    // The model you want
            User::class,       // The intermediate model (driver)
            'id',              // Foreign key on the User (driver) table
            'user_id',         // Foreign key on Vehicle table
            'driver_id',       // Local key on PassengerRequest (driver_id)
            'id'               // Local key on User table
        );
    }

    // public function interests()
    // {
    //     return $this->hasMany(PassengerRequestDriverInterest::class);
    // }

    public function interests()
    {
        return $this->hasMany(PassengerRequestDriverInterest::class, 'passenger_request_id');
    }






}
