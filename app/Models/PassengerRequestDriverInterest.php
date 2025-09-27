<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassengerRequestDriverInterest extends Model
{
    protected $fillable = ['passenger_request_id', 'driver_id'];

    public function driver() {
        return $this->belongsTo(User::class, 'driver_id');
    }


}

