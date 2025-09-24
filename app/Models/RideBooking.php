<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideBooking extends Model
{
    protected $fillable = [
        'ride_id', 'user_id', 'seats_booked', 'price', 'services', 'status'
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
}
