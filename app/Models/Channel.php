<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Channel extends Model
{
    protected $collection = 'channels';

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', '_id');
    }
}
