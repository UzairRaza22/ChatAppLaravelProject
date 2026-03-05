<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AdminSessionToken;

class CheckAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get Bearer token from request header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Admin token is missing.'
            ], 401);
        }

        // Find the session token in the DB
        $adminSession = AdminSessionToken::where('token', $token)->first();

        if (!$adminSession || !$adminSession->admin_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin token.'
            ], 403);
        }

        // Optionally, you can attach admin info to the request for downstream use
        $request->merge(['admin_id' => $adminSession->admin_id]);

        return $next($request);
    }
}
