<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use MongoDB\BSON\ObjectId;

class CheckWorkspacesExistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = data_get($request, 'user');
        
        // Check if user is authenticated
        if (!$user) {
            return response()->unauthorized('User not authenticated.');
        }
        
        // Check if workspace_id is provided in request body or route parameter
        $workspaceId = data_get($request, 'workspace_id') ?: data_get($request, 'route.id');
        
        if ($workspaceId) {
            // Get specific workspace
            $workspaces = Workspace::where('_id', $workspaceId)
                ->where('user_ids', data_get($user, '_id'))
                ->get();
                
            // If no workspace found with that ID, return empty collection
            if ($workspaces->isEmpty()) {
                return response()->notFound('Workspace not found or access denied.');
            }
            
            // Set both workspace and workspaces for flexibility
            $request->merge([
                'workspace' => $workspaces->first(),
                'workspaces' => $workspaces
            ]);
        } else {
            // Get all workspaces for user using user_ids field
            $workspaces = Workspace::where('user_ids', data_get($user, '_id'))->get();
            
            // Set only workspaces for all workspaces (no workspace property)
            $request->merge(['workspaces' => $workspaces]);
        }

        return $next($request);
    }
}
