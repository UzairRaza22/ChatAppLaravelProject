<?php

namespace App\Http\Middleware\Team;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceCreatorTeamMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Uzair ka tareeqa: User object request se nikaalna
        $user = $request->user(); 

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $workspaceId = $request->input('workspace_id'); 

        // Workspace find karna
        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->json(['message' => 'Workspace not found.'], 404);
        }

        // Creator ID check karna (Uzair ke code mein dono strings ya ObjectIDs match honi chahiye)
        if ($workspace->creator_id !== $user->_id) {
            return response()->json(['message' => 'Unauthorized access to workspace.'], 403);
        }

        // Uzair 'merge' use karta hai attributes ki bajaye
        $request->merge([
            'workspace' => $workspace,
        ]);

        return $next($request);
    }
}