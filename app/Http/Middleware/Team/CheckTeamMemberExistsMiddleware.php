<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTeamMemberExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $team = data_get($request, 'team'); 
        $memberIds = data_get($request, 'member_ids', []);

        if ($team && !empty($memberIds)) {
            $existingMembersRaw = data_get($team, 'members', []);
            
            if ($existingMembersRaw instanceof \Illuminate\Support\Collection) {
                $existingMembersRaw = $existingMembersRaw->toArray();
            }

            $safeExistingMembers = [];
            foreach ((array)$existingMembersRaw as $member) {
                $oid = data_get($member, '$oid');
                
                if (is_string($member)) {
                    $safeExistingMembers[] = $member;
                } elseif ($oid) {
                    $safeExistingMembers[] = (string)$oid;
                } else {
                    $safeExistingMembers[] = (string)$member;
                }
            }

            // 3. Duplicate check logic
            foreach ($memberIds as $id) {
                if (in_array((string)$id, $safeExistingMembers)) {
                    abort(409, 'User with ID ' . $id . ' is already a member of this team.');
                }
            }
        }

        return $next($request);
    }
}