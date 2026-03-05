<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class CheckTokensMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from request header
        $token = $request->header('X-ADMIN-TOKEN'); // or use Authorization if you prefer

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Admin token is missing.'
            ], 401);
        }

        // Check if there is any user with this token in admin_id column
        $adminUser = User::where('admin_id', $token)->first();

        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin token.'
            ], 401);
        }

        // Attach the admin user to the request for downstream use
        $request->setUserResolver(fn() => $adminUser);

        return $next($request);
    }
}
