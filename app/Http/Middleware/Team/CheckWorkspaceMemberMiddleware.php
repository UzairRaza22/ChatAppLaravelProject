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

        // --- Fix: Collection ko Array mein convert karna ---
        $workspaceMembers = $workspace->members;
        
        if ($workspaceMembers instanceof \Illuminate\Support\Collection) {
            $workspaceMembers = $workspaceMembers->toArray();
        } elseif (!is_array($workspaceMembers)) {
            $workspaceMembers = [];
        }

        $validUserIds = [];

        // Loop har email ko check karega, chahe array mein 1 ho ya 5
        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();

            if (!$user || !in_array($user->_id, $workspaceMembers)) {
                return response()->json([
                    'success' => false,
                    'message' => "The user with email {$email} is not a member of this workspace."
                ], 403);
            }

            $validUserIds[] = $user->_id;
        }

        // IDs ko merge kar dena taake Controller ko array milay
        $request->merge(['member_ids' => $validUserIds]);

        return $next($request);
    }
}