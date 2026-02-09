<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Channel extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'channels';

    const TYPE_DIRECT = 'direct';
    const TYPE_PUBLIC = 'public';
    const TYPE_PRIVATE = 'private';

    protected $fillable = [
        'name',
        'description',
        'type',
        'workspace_id',
        'team_id',
        'is_active',
        'user_ids',
        'settings',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'user_ids' => 'array',
        'settings' => 'array',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, null, 'channel_ids', 'user_ids');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'channel_id')->orderBy('created_at', 'desc');
    }

    public function addMember($userId)
    {
        if (!$this->user_ids) {
            $this->user_ids = [];
        }
        
        if (!in_array($userId, $this->user_ids)) {
            $this->user_ids[] = $userId;
            $this->save();
        }
    }

    public function removeMember($userId)
    {
        if ($this->user_ids) {
            $this->user_ids = array_values(array_filter($this->user_ids, function($id) use ($userId) {
                return $id !== $userId;
            }));
            $this->save();
        }
    }

    public function hasMember($userId)
    {
        return $this->user_ids && in_array($userId, $this->user_ids);
    }

    public function isDirect()
    {
        return $this->type === self::TYPE_DIRECT;
    }

    public function isPublic()
    {
        return $this->type === self::TYPE_PUBLIC;
    }

    public function isPrivate()
    {
        return $this->type === self::TYPE_PRIVATE;
    }

    public function scopeDirect($query)
    {
        return $query->where('type', self::TYPE_DIRECT);
    }

    public function scopePublic($query)
    {
        return $query->where('type', self::TYPE_PUBLIC);
    }

    public function scopePrivate($query)
    {
        return $query->where('type', self::TYPE_PRIVATE);
    }
}
