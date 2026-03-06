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
        $workspaceId = $request->workspace_id;
        $emails = $request->emails; // Yeh array hai (Postman se 1 email ho ya zyada)

        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace not found.'
            ], 404);
        }

        // Normalize member IDs from relation to plain strings for safe comparisons.
        $workspaceMembers = collect($workspace->members ?? [])
            ->map(function ($member) {
                if ($member instanceof User) {
                    return (string) $member->_id;
                }

                if (is_array($member)) {
                    return (string) ($member['_id'] ?? $member['id'] ?? '');
                }

                if (is_object($member) && isset($member->_id)) {
                    return (string) $member->_id;
                }

                return (string) $member;
            })
            ->filter()
            ->values()
            ->all();

        $validUserIds = [];

        // Loop har email ko check karega, chahe array mein 1 ho ya 5
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            $userId = (string) $user->_id;

            if (!in_array($userId, $workspaceMembers, true)) {
                return response()->json([
                    'success' => false,
                    'message' => "The user with email {$email} is not a member of this workspace."
                ], 403);
            }

            $validUserIds[] = $userId;
        }

        // IDs ko merge kar dena taake Controller ko array milay
        $request->merge(['member_ids' => $validUserIds]);

        return $next($request);
    }
}
