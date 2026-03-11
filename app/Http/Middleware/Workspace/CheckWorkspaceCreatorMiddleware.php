<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use MongoDB\BSON\ObjectId;

class CheckWorkspaceCreatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = data_get($request, 'user');
        if(!$user){
             return response()->unauthorized('Unauthorized');
        }

        // workspace ID is passed as a input named 'workspace'
        $workspaceId = data_get($request, 'workspace_id'); 
        $workspace = Workspace::where('_id', $workspaceId)->first();
      
        if(!$workspace){
            return response()->notFound('Workspace not found.');
        }

        if(data_get($workspace, 'creator_id') !== data_get($user, '_id')){
            return response()->unauthorized('Unauthorized access to workspace.');
        }
        $request->merge([
            'workspace' => $workspace,
        ]);
        

        return $next($request);
    }
}
