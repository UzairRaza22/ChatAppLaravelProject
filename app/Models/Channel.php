<?php

namespace App\Models;

use Illuminate\Support\Collection;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Channel extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'channels';

    protected $fillable = [
        'name',
        'workspace_id',
        'team_id',
        'type', // public/private/direct
        'created_by',
        'members', // array of {user_id, role}
    ];

    protected $casts = [
        'members' => 'array',
    ];

    public static function visibleForUserInWorkspace(string $userId, string $workspaceId): Collection
    {
        $teamIds = Team::where('workspace_id', $workspaceId)
            ->get()
            ->filter(function ($team) use ($userId) {
                $memberIds = collect($team->members ?? [])
                    ->map(fn ($id) => (string) $id)
                    ->all();

                return in_array($userId, $memberIds, true);
            })
            ->pluck('_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return self::where('workspace_id', $workspaceId)
            ->get()
            ->filter(function ($channel) use ($userId, $teamIds) {
                if ((string) $channel->type === 'direct') {
                    $directMemberIds = collect($channel->members ?? [])
                        ->map(fn ($member) => (string) data_get($member, 'user_id'))
                        ->filter()
                        ->all();

                    return in_array($userId, $directMemberIds, true);
                }

                return in_array((string) $channel->team_id, $teamIds, true);
            })
            ->values();
    }
}
