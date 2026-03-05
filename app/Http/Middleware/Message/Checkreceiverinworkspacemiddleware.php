<?php

namespace App\Http\Middleware\Message;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReceiverInWorkspaceMiddleware
{
    /**
     * For Direct Messages: ensure the receiver belongs to the same workspace.
     * Must run AFTER CheckWorkspaceMemberMiddleware so 'workspace' is in request.
     * Only runs when 'receiver_id' is present in the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $receiverId = $request->input('receiver_id');

        // Not a DM — skip this check
        if (!$receiverId) {
            return $next($request);
        }

        $workspace = data_get($request, 'workspace');
        $receiver  = User::where('_id', $receiverId)->first();

        if (!$receiver) {
            return response()->json([
                'message' => 'Receiver not found.'
            ], 404);
        }

        // Check receiver is a member of the workspace
        $isMember = $workspace->members()
            ->where('_id', $receiver->_id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'message' => 'Receiver is not a member of this workspace.'
            ], 403);
        }

        $request->merge(['receiver' => $receiver]);

        return $next($request);
    }
}
