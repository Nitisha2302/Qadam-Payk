<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierRequestCancellation extends Model
{
    use HasFactory;
     protected $fillable = [
        'courier_request_id',
        'cancelled_by_user_id',
        'cancelled_by',
        'reason'
    ];
    
}
