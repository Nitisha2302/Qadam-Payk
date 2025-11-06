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
        'language_code',
    ];

    // Optional: if you want to customize table name (default is 'services')
    // protected $table = 'services';
    // Accessor: replace service IDs with details
    protected $casts = [
        'services' => 'array', // stored as JSON
    ];
    public function getServicesAttribute($value)
    {
        $ids = json_decode($value, true);

        if (empty($ids) || !is_array($ids)) {
            return [];
        }

        return Service::whereIn('id', $ids)->get(['id','service_name','service_image']);
    }
}
