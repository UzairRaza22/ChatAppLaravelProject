<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Team extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'teams';

    protected $fillable = [
        'workspace_id', // Jis workspace mein team ban rahi hai
        'name',
        'description',
        'creator_id',   // Team banane wala (Admin)
        'members'       // Array of User IDs
    ];
}