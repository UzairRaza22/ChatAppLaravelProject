<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use App\Models\Workspace;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceExistsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = $request->route('workspace_id');

        if ($workspaceId && !Workspace::find($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace not found.'
            ], 404);
        }

        return $next($request);
    }
}
