<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{

    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'name',
        'type',
        'note',
        'photo_url',
        'distance',
    ];

    protected $dates = [
        'started_at',
        'ended_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
