<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    
    protected $collection = 'teams';

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'creator_id',
        'members',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    
    // Team jis workspace se taluq rakhti hai
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', '_id');
    }

    // Team ka creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', '_id');
    }
}