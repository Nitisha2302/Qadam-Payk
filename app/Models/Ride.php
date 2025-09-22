<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    protected $casts = [
    'services' => 'array',   // 👈 this tells Laravel to store/retrieve JSON
    'accept_parcel' => 'boolean',
    'ride_date' => 'date',
    'ride_time' => 'datetime:H:i',
];
}
