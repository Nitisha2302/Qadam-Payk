<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'media', 'route', 'city', 'description', 'category', 'reported', 'expires_at'
    ];

    protected $dates = ['expires_at'];

    // Check if story is still active
    public function isActive()
    {
        return $this->expires_at->isFuture();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reports()
    {
        return $this->hasMany(StoryReport::class);
    }

    public function views()
    {
        return $this->hasMany(StoryView::class);
    }

    public function viewers()
    {
        return $this->belongsToMany(User::class, 'story_views')
            ->select('users.id', 'users.name', 'users.image')
            ->withTimestamps();
    }


}
