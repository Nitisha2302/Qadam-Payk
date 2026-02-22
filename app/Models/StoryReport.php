<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryReport extends Model
{
    protected $fillable = [
        'story_id',
        'user_id',
        'reason',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
