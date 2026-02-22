<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pickup_location',
        'drop_location',
        'distance',
        'time',
        'trip_type',
        'sender_name',
        'sender_phone',
        'sender_landmark',
        'receiver_name',
        'receiver_phone',
        'receiver_landmark',
        'package_description',
        'package_size',
        'instruction',
        'suggested_price',
        'payment_method',
        'paid_by',
        'status',
        'accepted_driver_id',
        'expires_at',
        'pickup_latitude',
        'pickup_longitude',
        'drop_latitude',
        'drop_longitude',
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function acceptedDriver()
    {
        return $this->belongsTo(User::class, 'accepted_driver_id');
    }

    public function interests()
    {
        return $this->hasMany(CourierRequestDriverInterest::class, 'courier_request_id');
    }

    
}
