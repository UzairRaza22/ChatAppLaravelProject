<?php

namespace App\Http\Middleware\Workspace;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Workspace;
use MongoDB\BSON\ObjectId;

class CheckWorkspaceExistsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $workspaceId = data_get($request, 'workspace_id');

        $workspace = Workspace::where('_id', $workspaceId)->first();

        if (!$workspace) {
            return response()->notFound('Workspace not found.');
        }

        return $next($request);
    }
}
