<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $collection = 'channels';

    protected $fillable = [
        'name',
        'description',
        'workspace_id',
        'creator_id',
    ];

    protected function casts(): array
    {
        return [
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
}
