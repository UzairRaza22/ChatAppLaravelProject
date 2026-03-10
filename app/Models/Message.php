<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $collection = 'messages';

    protected $fillable = [
        'workspace_id',
        'sender_id',
        'receiver_id',
        'channel_id',
        'message_type',
        'content',
        'file_path',
        'file_name',
        'file_mime',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', '_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', '_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', '_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id', '_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Helpers
    |--------------------------------------------------------------------------
    */

    public static function add(array $data): self
    {
        return self::create($data);
    }

    public static function edit(array $data, self $message): self
    {
        $updateData = [];

        if (isset($data['content'])) {
            $updateData['content'] = $data['content'];
        }

        if (isset($data['file_path'])) {
            $updateData['file_path']  = $data['file_path'];
            $updateData['file_name']  = $data['file_name']  ?? null;
            $updateData['file_mime']  = $data['file_mime']  ?? null;
        }

        $message->update($updateData);

        return $message;
    }
}
