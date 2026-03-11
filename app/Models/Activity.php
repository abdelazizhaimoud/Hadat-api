<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "category",
        "location",
        "date_time",
        "max_participants",
        "host_id",
    ];

    public function host(){
        return $this->belongsTo(User::class);
    }

    public function participants(){
        return $this->belongsToMany(User::class, "activity_user");
    }
}
