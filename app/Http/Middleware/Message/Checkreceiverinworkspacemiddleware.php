<?php

namespace App\Http\Middleware\Message;

use App\Models\User;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckReceiverInWorkspaceMiddleware
{
    /**
     * For Direct Messages:
     * 1. Check receiver exists
     * 2. Find a workspace where BOTH sender and receiver are members
     * 3. Merge receiver + workspace into request
     *
     * Skipped automatically if receiver_id is not in the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $receiverId = $request->input('receiver_id');

        // Not a DM — skip
        if (!$receiverId) {
            return $next($request);
        }

        $sender = $request->user();

        // Check receiver exists
        $receiver = User::where('_id', $receiverId)->first();

        if (!$receiver) {
            return response()->notFound('Receiver not found.');
        }

        // Check receiver is a member of the workspace
        $isMember = $workspace->members()
            ->where('_id', $receiver->_id)
            ->exists();

        if (!$isMember) {
            return response()->forbidden('Receiver is not a member of this workspace.');
        }

        // Get all workspaces and find one where both users are members
        // Using get() + contains() because MongoDB does not support whereHas()
        $sharedWorkspace = null;

        $allWorkspaces = Workspace::all();

        foreach ($allWorkspaces as $workspace) {
            $members = $workspace->members()->get();

            $senderIsMember   = $members->contains('_id', (string) $sender->_id);
            $receiverIsMember = $members->contains('_id', (string) $receiver->_id);

            if ($senderIsMember && $receiverIsMember) {
                $sharedWorkspace = $workspace;
                break;
            }
        }

        if (!$sharedWorkspace) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver is not in any shared workspace with you.'
            ], 403);
        }

        $request->merge([
            'receiver'  => $receiver,
            'workspace' => $sharedWorkspace,
        ]);

        return $next($request);
    }
}
