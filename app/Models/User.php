<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $attributes = [
        'is_active' => false,
        'workspace_ids' => [],
        'access_token' => null,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'workspace_ids',
        'access_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function createdWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'creator_id', '_id');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, null, 'user_ids', 'workspace_ids');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, null, 'user_ids', 'team_ids');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, null, 'task_ids', 'assignee_ids');
    }


    public static function add($data)
    {
        return self::create([
            'name' => data_get($data, 'name'),
            'email' => data_get($data, 'email'),
            'password' => data_get($data, 'password'),
            'is_active' => false,
        ]);
    }
}
