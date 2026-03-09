<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class FcmToken extends Model
{
    protected $collection = 'fcm_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query; // Modify as needed if active condition applies
    }
}
