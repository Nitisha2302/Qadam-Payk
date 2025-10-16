<?php

// app/Models/UserLang.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLang extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'language',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

