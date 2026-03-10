<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;

class CheckMembersExistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = data_get($request, 'workspace_id');
        $userIds = data_get($request, 'user_ids');

        // Get workspace directly
        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        // Get existing member IDs
        $existingMemberIds = $workspace->members()->pluck('_id')->toArray();

        // Convert all IDs to strings for proper comparison
        $existingMemberIds = array_map('strval', $existingMemberIds);
        $userIds = array_map('strval', $userIds);

        // Find users that are NOT members
        $nonExistingMembers = array_diff($userIds, $existingMemberIds);

        if (!empty($nonExistingMembers)) {
            $nonExistingMemberIds = implode(', ', $nonExistingMembers);
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this workspace.'
            ], 400);
        }

        // Set workspace in request for other middleware/controller
        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
