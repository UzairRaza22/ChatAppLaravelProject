<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'channels';

    protected $fillable = [
        'name',
        'workspace_id',
        'team_id',
        'type', // public/private/direct
        'members', // array of {user_id, role}
    ];

    protected $casts = [
        'members' => 'array',
    ];
}