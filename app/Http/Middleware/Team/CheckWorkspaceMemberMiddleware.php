<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceMemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
 
    $workspaceId = data_get($request, 'workspace_id');
        $userIds = data_get($request, 'user_ids'); 

        if (!is_array($userIds)) {
            $userIds = $userIds ? [$userIds] : [];
        }

        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            abort(404, 'Workspace not found.');
        }

        $workspaceMembers = data_get($workspace, 'members', []);
        if ($workspaceMembers instanceof \Illuminate\Support\Collection) {
            $workspaceMembers = $workspaceMembers->toArray();
        }

        $safeWorkspaceMemberIds = [];
        foreach ((array)$workspaceMembers as $member) {
            
            if (is_string($member) && str_starts_with($member, 'a:')) {
                $unserialized = @unserialize($member);
                $id = data_get($unserialized, 'id') ?? data_get($unserialized, '_id');
                if ($id) {
                    $safeWorkspaceMemberIds[] = (string)$id;
                }
            } 
            elseif (is_string($member)) {
                $safeWorkspaceMemberIds[] = $member;
            } 
            elseif (is_array($member) || is_object($member)) {
                $id = data_get($member, 'id') 
                      ?? data_get($member, '_id.$oid') 
                      ?? data_get($member, '_id') 
                      ?? data_get($member, '$oid');

                if ($id) {
                    $safeWorkspaceMemberIds[] = (string)$id;
                }
            }
        }

        $validUserIds = [];
        foreach ($userIds as $userId) {
            if (!is_string($userId)) continue;

            $user = User::find($userId);

            $currentUserId = (string) data_get($user, '_id');

            if (!$user || !in_array($currentUserId, $safeWorkspaceMemberIds)) {
                abort(403, "The user with ID " . $userId . " is not a member of this workspace.");
            }

            $validUserIds[] = $currentUserId;
        }

        $request->merge(['member_ids' => $validUserIds]);

        return $next($request);
    }
}