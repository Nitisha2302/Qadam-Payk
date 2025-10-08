<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideBooking extends Model
{
    protected $fillable = [
        'ride_id', 'user_id', 'seats_booked', 'price', 'services', 'status','type','active_status','ride_date','ride_time','request_id','comment'
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RideBooking.php
    public function getServicesDetailsAttribute()
    {
        // return full service details for the stored IDs
        return \App\Models\Service::whereIn('id', $this->services ?? [])
                                ->get(['id', 'service_name', 'service_image']);
    }




    
}
