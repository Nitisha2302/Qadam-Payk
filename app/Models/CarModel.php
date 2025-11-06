<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $fillable = [
        'model_name', 'brand', 'color', 'seats','language_code'
    ];

}
