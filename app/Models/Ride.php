<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ride extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'pickup_location',
        'destination',
        'number_of_seats',
        'price',
        'ride_date',
        'ride_time',
        'services',
        'accept_parcel',
    ];

    /**
     * Ride belongs to a driver (user).
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ride belongs to a vehicle.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rideBookings()
    {
        return $this->hasMany(RideBooking::class);
    }
    protected $casts = [
    'services' => 'array',   // 👈 this tells Laravel to store/retrieve JSON
    'accept_parcel' => 'boolean',
    'ride_date' => 'date',
    'ride_time' => 'datetime:H:i',
  ];

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
}
