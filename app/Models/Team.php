<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Team extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'teams';

    protected $fillable = [
        'name',
        'description',
        'workspace_id',
        'is_active',
        'user_ids',
        'settings'
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

    public function members()
    {
        return $this->belongsToMany(User::class, null, 'team_ids', 'user_ids');
    }

    public function channels()
    {
        return $this->hasMany(Channel::class, 'team_id');
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
}
