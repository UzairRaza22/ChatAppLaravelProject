<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Workspace extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'workspaces';

    protected $fillable = [
        'name',
        'description',
        'slug',
        'owner_id',
        'is_active',
        'user_ids',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'user_ids' => 'array',
        'settings' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function teams()
    {
        return $this->hasMany(Team::class, 'workspace_id');
    }

    public function channels()
    {
        return $this->hasMany(Channel::class, 'workspace_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, null, 'workspace_ids', 'user_ids');
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

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
