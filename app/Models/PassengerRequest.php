<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class PassengerRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'pickup_location',
        'destination',
        'number_of_seats',
        'pickup_contact_name',
        'pickup_contact_no',
        'drop_contact_name',
        'drop_contact_no',
        'parcel_details',
        'parcel_images',
        'ride_date',
        'ride_time',
        'services',
        'driver_id',
        'status',
    ];

    protected $casts = [
        'services' => 'array',
         'type' => 'integer', // 0 = ride, 1 = parcel
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // ✅ Format ride_date as d-m-Y
    public function getRideDateAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->format('d-m-Y')
            : null;
    }

    // ✅ Format ride_time as H:i
    public function getRideTimeAttribute($value)
    {
        return $value
            ? Carbon::parse($value)->format('H:i')
            : null;
    }
}
