<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Conversation extends Model
{
    // Fields that can be mass-assigned
    protected $fillable = [
        'user_one_id','user_two_id','last_message_at','last_message_preview','last_message_id'
    ];

    // Relationship: a conversation has many messages
    public function messages() {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    // Get conversation between 2 users or create if not exists
    public static function between($a, $b) {
        $a = (int)$a; $b = (int)$b;
        [$one,$two] = $a <= $b ? [$a,$b] : [$b,$a]; // Order IDs to prevent duplicates
        return self::firstOrCreate(['user_one_id'=>$one,'user_two_id'=>$two]);
    }

    // Relations with User
    public function userOne() {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo() {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    // Get the other participant's ID
    public function otherUserId($userId) {
        return $this->user_one_id == $userId ? $this->user_two_id : $this->user_one_id;
    }

    // Get the other participant's ID
    // Get the other participant's full User model
    public function otherUser($userId) {
        return $this->user_one_id == $userId ? $this->userTwo : $this->userOne;
    }

    

    
}
