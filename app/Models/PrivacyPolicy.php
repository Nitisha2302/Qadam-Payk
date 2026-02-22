<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    use HasFactory;

    protected $table = 'privacy_policies';
    public $timestamps = false; // Set true if your table uses timestamps accordingly
    protected $fillable = ['title', 'content', 'created_at', 'updated_at'];

    //code by anukool
    // protected $fillable = [
    //     'title',
    //     'content',
    // ];
    //end code by anukool
}
