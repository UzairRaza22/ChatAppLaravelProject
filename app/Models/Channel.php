<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

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

    protected function casts(): array
    {
        return [
            'members' => 'json', // Changed from 'array' to 'json' to handle JSON strings
            'join_requests' => 'json',
            'deleted_at' => 'datetime',
        ];
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', '_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'channel_id', '_id');
    }

    public function members()
    {
        // Debug logging to understand members data storage
        \Log::info('=== CHANNEL MEMBERS DEBUG ===');
        \Log::info('Raw members attribute: ' . json_encode($this->attributes['members'] ?? 'null'));
        \Log::info('Raw members relation: ' . json_encode($this->members ?? 'null'));
        \Log::info('Members count: ' . count($this->members ?? []));
        
        return $this->belongsToMany(User::class, null, 'channel_ids', 'members');
    }
}
