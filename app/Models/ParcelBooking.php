<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcelBooking extends Model
{
    protected $fillable = [
        'user_id', 
        'pickup_city', 'pickup_name', 'pickup_contact',
        'drop_city', 'drop_name', 'drop_contact',
        'parcel_description', 'parcel_images', 'status'
    ];

    protected $casts = [
        'parcel_images' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
