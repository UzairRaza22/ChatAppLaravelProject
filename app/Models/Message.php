<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Message extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'messages';

    protected $fillable = [
        'content',
        'channel_id',
        'sender_id',
        'message_type',
        'file_id',
        'file_name',
        'file_size',
        'file_mime_type',
        'file_url',
        'is_edited',
        'edited_at',
        'reply_to_id',
        'reactions',
        'mentions',
        'is_deleted',
        'deleted_at'
    ];

    protected $appends = ['file_url'];

    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'reactions' => 'array',
        'mentions' => 'array',
        'file_size' => 'integer',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    public function addReaction($userId, $emoji)
    {
        if (!$this->reactions) {
            $this->reactions = [];
        }

        $existingReactionIndex = null;
        foreach ($this->reactions as $index => $reaction) {
            if ($reaction['emoji'] === $emoji) {
                $existingReactionIndex = $index;
                break;
            }
        }

        if ($existingReactionIndex !== null) {
            if (!in_array($userId, $this->reactions[$existingReactionIndex]['user_ids'])) {
                $this->reactions[$existingReactionIndex]['user_ids'][] = $userId;
                $this->reactions[$existingReactionIndex]['count']++;
            }
        } else {
            $this->reactions[] = [
                'emoji' => $emoji,
                'user_ids' => [$userId],
                'count' => 1
            ];
        }

        $this->save();
    }

    public function removeReaction($userId, $emoji)
    {
        if (!$this->reactions) {
            return;
        }

        foreach ($this->reactions as $index => $reaction) {
            if ($reaction['emoji'] === $emoji) {
                $userIndex = array_search($userId, $reaction['user_ids']);
                if ($userIndex !== false) {
                    unset($this->reactions[$index]['user_ids'][$userIndex]);
                    $this->reactions[$index]['user_ids'] = array_values($this->reactions[$index]['user_ids']);
                    $this->reactions[$index]['count']--;

                    if ($this->reactions[$index]['count'] <= 0) {
                        unset($this->reactions[$index]);
                        $this->reactions = array_values($this->reactions);
                    }
                    
                    $this->save();
                }
                break;
            }
        }
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_id) {
            return url("/api/files/{$this->file_id}/download");
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeInChannel($query, $channelId)
    {
        return $query->where('channel_id', $channelId);
    }
}
