<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'notification_type',
        'booking_id',
        'notification_created_at',
        'image'
    ];

    // Optional: relation to user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optional: relation to booking
    public function booking()
    {
        return $this->belongsTo(RideBooking::class, 'booking_id');
    }
}
