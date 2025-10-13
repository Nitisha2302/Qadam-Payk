<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'state',
        'country',
    ];

    public function rides() {
        return $this->hasMany(Ride::class);
    }
}
