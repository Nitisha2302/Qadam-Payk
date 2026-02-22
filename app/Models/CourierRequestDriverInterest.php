<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierRequestDriverInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_request_id',
        'driver_id',
        'driver_price',
        'message'
    ];

    public function courierRequest()
    {
        return $this->belongsTo(CourierRequest::class, 'courier_request_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
    
}
