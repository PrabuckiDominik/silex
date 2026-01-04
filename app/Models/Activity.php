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
        'photo_path',
        'distance',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? route('activities.photo', $this) : null;
    }
}
