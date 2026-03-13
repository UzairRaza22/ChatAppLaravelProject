<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Activity extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'activities';
    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'array',
        'response_data'   => 'array',
        'created_at'      => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}