<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierLiveTracking extends Model
{
    protected $fillable = [
        'courier_request_id',
        'driver_id',
        'latitude',
        'longitude',
        'tracked_at'
    ];

    public function courier()
    {
        return $this->belongsTo(CourierRequest::class, 'courier_request_id');
    }
}