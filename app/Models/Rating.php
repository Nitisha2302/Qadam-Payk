<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'ride_id',
        'reviewer_id',
        'reviewed_id',
        'rating',
        'review'
    ];

    public function ride()
    {
        return $this->belongsTo(\App\Models\Ride::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }

    public function reviewed()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_id');
    }
}
