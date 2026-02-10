<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->extractTokenFromRequest($request);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or empty token'
            ], 401);
        }

        $apiToken = ApiToken::findValidToken($token);
        
        if (!$apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or empty token'
            ], 401);
        }

        // Update token usage information
        $apiToken->updateUsage($request);

        // Get the user
        $user = User::find($apiToken->user_id);
        
        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or empty token'
            ], 401);
        }

        // Authenticate the user
        auth()->login($user);
        
        // Add token and user to request for later use
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Add token to request attributes
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }

    /**
     * Extract token from request
     */
    private function extractTokenFromRequest(Request $request)
    {
        // Try Bearer token first
        $token = $request->bearerToken();
        
        if ($token) {
            return $token;
        }

        // Try Authorization header without Bearer
        $authHeader = $request->header('Authorization');
        if ($authHeader && !str_starts_with($authHeader, 'Bearer ')) {
            return $authHeader;
        }

        // Try query parameter
        return $request->query('api_token');
    }
}
