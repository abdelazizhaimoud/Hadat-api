<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        "user_id",
        "activity_id",
        "content",
        "likes",
        "dislikes",
        "shares"
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function activity(){
        return $this->belongsTo(Activity::class);
    }
}
