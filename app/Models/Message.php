<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Message extends Model
{
    use SoftDeletes, Searchable;

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
        'read_by',
        'reactions',
    ];

    protected $casts(): array
    {
        return [
            'deleted_at' => 'datetime',
            'read_by' => 'array',
            'reactions' => 'array',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Laravel Scout Configuration
    |--------------------------------------------------------------------------
    */

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $searchable = [
            'id' => (string) $this->_id,
            'content' => $this->content,
            'message_type' => $this->message_type,
            'workspace_id' => (string) $this->workspace_id,
            'channel_id' => (string) $this->channel_id,
            'sender_id' => (string) $this->sender_id,
            'created_at' => $this->created_at?->timestamp,
        ];

        // Include sender name if relationship is loaded
        if ($this->relationLoaded('sender') && $this->sender) {
            $searchable['sender_name'] = $this->sender->name;
        }

        // Include channel name if relationship is loaded
        if ($this->relationLoaded('channel') && $this->channel) {
            $searchable['channel_name'] = $this->channel->name;
        }

        return $searchable;
    }

    /**
     * Get the Scout index name for the model.
     */
    public function searchableAs(): string
    {
        return 'messages_index';
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['sender', 'channel']);
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        // Only index non-deleted messages with content
        return !$this->trashed() && !empty($this->content);
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

    /**
     * Atomically add $userId to the read_by array of the given messages.
     * Only updates messages that belong to $channelId.
     * Uses $addToSet to avoid duplicates (idempotent).
     *
     * @return int Number of modified documents.
     */
    public static function markReadBy(string $channelId, array $messageIds, string $userId): int
    {
        $result = self::whereIn('_id', $messageIds)
            ->where('channel_id', $channelId)
            ->push('read_by', $userId, true); // true = addToSet (unique)

        return $result;
    }

    /**
     * Toggle an emoji reaction for a user on a message.
     * Uses $addToSet / $pull for atomic concurrency safety.
     * After a pull, if the emoji array is empty, the key is unset.
     *
     * @return self Fresh message instance.
     */
    public static function toggleReaction(self $message, string $userId, string $emoji): self
    {
        $reactions  = $message->reactions ?? [];
        $emojiUsers = $reactions[$emoji] ?? [];

        if (in_array($userId, $emojiUsers)) {
            // Remove user from the emoji array
            $message->pull("reactions.{$emoji}", $userId);

            // Re-fetch to check if array is now empty
            $message->refresh();
            $fresh     = $message;
            $refreshed = $fresh->reactions ?? [];

            if (isset($refreshed[$emoji]) && empty($refreshed[$emoji])) {
                // Remove the now-empty emoji key entirely
                $fresh->unset("reactions.{$emoji}");
                $fresh->refresh();
            }
        } else {
            // Add user to the emoji array
            $message->push("reactions.{$emoji}", $userId, true); // true = addToSet
            $message->refresh();
        }

        return $message->fresh();
    }
}
