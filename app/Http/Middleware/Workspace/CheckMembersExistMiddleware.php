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

        // Get existing member IDs from both members relationship and user_ids field
        $memberIds = [];
        
        // From members relationship (pivot table)
        $memberIds = array_merge($memberIds, $workspace->members()->pluck('_id')->toArray());
        
        // From user_ids field (if it exists)
        if ($workspace->user_ids) {
            $memberIds = array_merge($memberIds, (array) $workspace->user_ids);
        }
        
        // Convert all IDs to strings for proper comparison
        $existingMemberIds = array_map('strval', $memberIds);
        $userIds = array_map('strval', $userIds);
        
        // Remove duplicates and re-index
        $existingMemberIds = array_values(array_unique($existingMemberIds));
        
        // Find users that are NOT members
        $nonExistingMembers = array_diff($userIds, $existingMemberIds);

        if (!empty($nonExistingMembers)) {
            $nonExistingMemberIds = implode(', ', $nonExistingMembers);
            return response()->forbidden('User is not a member of this workspace.');
        }

        // Set workspace in request for other middleware/controller
        $request->merge(['workspace' => $workspace]);

        return $next($request);
    }
}
