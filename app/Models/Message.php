<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

if (trait_exists(\Laravel\Scout\Searchable::class)) {
    trait MessageSearchable
    {
        use Searchable;
    }
} else {
    trait MessageSearchable
    {
        // Scout not installed; keep model functional without search indexing.
    }
}

class Message extends Model
{
    use SoftDeletes, MessageSearchable;

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
        'schedule_time',
        'status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'schedule_time' => 'datetime',
        // read_by and reactions removed from casts —
        // MongoDB already returns them as arrays, casting causes double-decode
    ];

    /**
     * Always return read_by as a clean array.
     */
    public function getReadByAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        return is_array($value) ? $value : [];
    }

    /**
     * Always return reactions as a clean array.
     */
    public function getReactionsAttribute($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        return is_array($value) ? $value : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Laravel Scout Configuration
    |--------------------------------------------------------------------------
    */

    public function toSearchableArray(): array
    {
        $searchable = [
            'id'           => (string) $this->_id,
            'content'      => $this->content,
            'message_type' => $this->message_type,
            'workspace_id' => (string) $this->workspace_id,
            'channel_id'   => (string) $this->channel_id,
            'sender_id'    => (string) $this->sender_id,
            'created_at'   => $this->created_at?->timestamp,
        ];

        if ($this->relationLoaded('sender') && $this->sender) {
            $searchable['sender_name'] = $this->sender->name;
        }

        if ($this->relationLoaded('channel') && $this->channel) {
            $searchable['channel_name'] = $this->channel->name;
        }

        return $searchable;
    }

    public function searchableAs(): string
    {
        return 'messages_index';
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with(['sender', 'channel']);
    }

    public function shouldBeSearchable(): bool
    {
        if ($this->trashed() || empty($this->content)) {
            return false;
        }

        if (!$this->status) {
            return true;
        }

        return $this->status === 'sent';
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
            $updateData['file_path'] = $data['file_path'];
            $updateData['file_name'] = $data['file_name'] ?? null;
            $updateData['file_mime'] = $data['file_mime'] ?? null;
        }

        $message->update($updateData);

        return $message;
    }

    public static function markReadBy(string $channelId, array $messageIds, string $userId): int
    {
        return self::whereIn('_id', $messageIds)
            ->where('channel_id', $channelId)
            ->push('read_by', $userId, true);
    }

    /**
     * Smart reaction handling with replacement and double-click deletion
     * 
     * Logic:
     * 1. Double-click (same emoji) → Delete reaction
     * 2. Replace (different emoji) → Remove old reaction, add new one
     * 3. New emoji → Add reaction
     * 4. Automatically removes emoji key if no users left
     * 
     * @param self $message
     * @param string $userId
     * @param string $emoji - New emoji to react with
     * @param string|null $userCurrentReaction - User's current reaction (for smart replacement)
     * @param bool $isDoubleClick - Whether this is a double-click action
     * @return self
     */
    public static function toggleReaction(
        self $message,
        string $userId,
        string $emoji,
        ?string $userCurrentReaction = null,
        bool $isDoubleClick = false
    ): self {
        $reactions = $message->reactions ?? [];

        // Case 1: Double-click on same emoji → Delete reaction
        if ($isDoubleClick && $userCurrentReaction && $userCurrentReaction === $emoji) {
            $message->pull("reactions.{$emoji}", $userId);
            $message->refresh();

            // Remove emoji key if no users left
            $refreshed = $message->reactions ?? [];
            if (isset($refreshed[$emoji]) && empty($refreshed[$emoji])) {
                $message->unset("reactions.{$emoji}");
                $message->refresh();
            }
        }
        // Case 2: Replace - user has different emoji, remove old and add new
        else if ($userCurrentReaction && $userCurrentReaction !== $emoji) {
            // Remove from old emoji
            $message->pull("reactions.{$userCurrentReaction}", $userId);
            $message->refresh();

            // Clean up old emoji if empty
            $refreshed = $message->reactions ?? [];
            if (isset($refreshed[$userCurrentReaction]) && empty($refreshed[$userCurrentReaction])) {
                $message->unset("reactions.{$userCurrentReaction}");
            }

            // Add new emoji reaction
            $message->push("reactions.{$emoji}", $userId, true);
            $message->refresh();
        }
        // Case 3: New emoji (user has no current reaction) → Add reaction
        else if (!$userCurrentReaction) {
            $message->push("reactions.{$emoji}", $userId, true);
            $message->refresh();
        }
        // Case 4: User clicked same emoji (but not flagged as double-click) → Do nothing or treat as toggle
        // This maintains backward compatibility if frontend doesn't send double-click flag
        else if ($userCurrentReaction === $emoji) {
            $emojiUsers = $reactions[$emoji] ?? [];
            if (in_array($userId, $emojiUsers)) {
                $message->pull("reactions.{$emoji}", $userId);
                $message->refresh();

                $refreshed = $message->reactions ?? [];
                if (isset($refreshed[$emoji]) && empty($refreshed[$emoji])) {
                    $message->unset("reactions.{$emoji}");
                    $message->refresh();
                }
            }
        }

        return $message->fresh();
    }
}
