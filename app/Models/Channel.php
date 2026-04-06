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
        'type',
        'created_id',
        'direct_id',
        'members',
        'join_requests',
    ];

    protected function casts(): array
    {
        return [
            'members'      => 'json',
            'join_requests'=> 'json',
            'deleted_at'   => 'datetime',
        ];
    }

    /**
     * Always returns members as a clean array regardless of how
     * MongoDB stored it (JSON string, array, or null).
     */
    public function getMembersAttribute($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($value) ? $value : [];
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', '_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'channel_id', '_id');
    }
}