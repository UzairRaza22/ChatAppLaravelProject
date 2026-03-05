<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckUserExistsForImpersonationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->route('user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $request->merge(['impersonate_user' => $user]);

        return $next($request);
    }
}
