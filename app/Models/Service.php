<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'service_image',
        'service_name',
    ];

    // Optional: if you want to customize table name (default is 'services')
    // protected $table = 'services';
}
