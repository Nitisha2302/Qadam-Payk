<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideBooking extends Model
{
    protected $fillable = [
        'ride_id',
        'request_id',
        'user_id',
        'seats_booked',
        'price',
        'services',
        'status',
        'type',
        'active_status',
        'ride_date',
        'ride_time',
        'comment',
    ];

    protected $casts = [
        'services' => 'array', // ensures services JSON is cast to array
    ];

    /**
     * Relation to the ride (if booking is linked to a ride)
     */
    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    /**
     * Relation to the passenger request (if booking is linked to a request)
     */
    public function request()
    {
        return $this->belongsTo(\App\Models\PassengerRequest::class, 'request_id');
    }

    /**
     * Relation to the passenger (user)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to driver if booking is linked to a ride
     */
    public function rideDriver()
    {
        return $this->hasOneThrough(
            User::class,
            Ride::class,
            'id',       // rides.id
            'id',       // users.id
            'ride_id',  // ride_bookings.ride_id
            'user_id'   // rides.user_id
        );
    }

    /**
     * Relation to driver if booking is linked to a request
     */
public function requestDriver()
{
    return $this->hasOneThrough(
        \App\Models\User::class,             // final model (User)
        \App\Models\PassengerRequest::class, // intermediate model
        'id',       // passenger_requests.id (foreign key on intermediate table)
        'id',       // users.id (foreign key on final table)
        'request_id', // ride_bookings.request_id
        'driver_id'  // passenger_requests.driver_id
    );
}


    /**
     * Helper accessor to get the driver dynamically
     */
    public function getDriverAttribute()
    {
        if ($this->ride_id) {
            return $this->rideDriver()->first();
        }

        if ($this->request_id) {
            return $this->requestDriver()->first();
        }

        return null;
    }

    /**
     * Accessor for pickup location
     */
    public function getPickupLocationAttribute($value)
    {
        if ($value) return $value;
        if ($this->ride?->pickup_location) return $this->ride->pickup_location;
        if ($this->request?->pickup_location) return $this->request->pickup_location;
        return null;
    }

    /**
     * Accessor for destination
     */
    public function getDestinationAttribute($value)
    {
        if ($value) return $value;
        if ($this->ride?->destination) return $this->ride->destination;
        if ($this->request?->destination) return $this->request->destination;
        return null;
    }

    /**
     * Services details accessor
     */
    public function getServicesDetailsAttribute()
    {
        $serviceIds = $this->services;

        // Decode JSON if stored as string
        if (is_string($serviceIds)) {
            $serviceIds = json_decode($serviceIds, true);
        }

        if (empty($serviceIds) || !is_array($serviceIds)) {
            return collect(); // return empty collection
        }

        return \App\Models\Service::whereIn('id', $serviceIds)
            ->get(['id', 'service_name', 'service_image']);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }


    // RideBooking.php
    // public function getStatusLabelAttribute()
    // {
    //     if ($this->status === 'cancelled') {
    //         return ['text' => 'Cancelled', 'class' => 'bg-danger'];
    //     }

    //     if ($this->status === 'confirmed' && $this->active_status == 0) {
    //         return ['text' => 'Confirmed', 'class' => 'bg-success'];
    //     }

    //     return match ($this->active_status) {
    //         1 => ['text' => 'Active', 'class' => 'bg-success'],
    //         2 => ['text' => 'Completed', 'class' => 'bg-primary'],
    //         default => ['text' => 'Pending', 'class' => 'bg-warning text-dark'],
    //     };
    // }
}
