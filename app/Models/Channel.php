<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'channels';

    protected $fillable = [
        'id',
        'name',
        'workspace_id',
        'team_id',
        'type', // public/private/direct
        'created_id',
        'direct_id',
        'members', // array of {user_id, role}
        'join_requests',
    ];

    protected $casts = [
        'members' => 'array',
        'join_requests' => 'array',
    ];
}