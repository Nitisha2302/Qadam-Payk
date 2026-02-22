<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id','sender_id','message','type','meta','read_at','send_at'
    ];

    // Automatically convert meta JSON to array
    protected $casts = [
        'meta'=>'array',
        'read_at'=>'datetime',
        'send_at' => 'datetime',
    ];

    // Message belongs to a sender (User)
    public function sender() {
        return $this->belongsTo(User::class,'sender_id');
    }

    // Message belongs to a conversation
    public function conversation() {
        return $this->belongsTo(Conversation::class);
    }
}
