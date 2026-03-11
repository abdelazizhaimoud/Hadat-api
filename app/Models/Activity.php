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

    public function user(){
        return $this->belongsTo(User::class);
    }
}
