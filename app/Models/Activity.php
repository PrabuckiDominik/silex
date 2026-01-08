<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'note',
        'photo_path',
        'distance',
        'time', // sekundy
    ];

    protected $casts = [
        'time' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? route('activities.photo', $this) : null;
    }
}
