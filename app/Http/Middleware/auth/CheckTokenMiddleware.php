<?php

namespace App\Http\Middleware\auth;

use Illuminate\Http\Request;
use App\Models\{SessionToken, ForgetToken, User};
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Token can be provided in:
     * 1. Route parameter: /api/auth/verify/{token}
     * 2. Custom header: token (e.g., token: abc123)
     * 3. Request body: token=abc123
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $tokenType = null): Response
    {
        // For signup verification, check if email exists first
        if ($tokenType === 'signup_verification_token') {
            $email = data_get($request, 'email');
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->notFound('Email not registered.');
            }
        }

        // Get token from route, custom header, or request body
        $token = data_get($request, 'route.token') ?? 
                 $request->header('token') ?? 
                 data_get($request, 'token');
        
        if (!$token) {
            return response()->unauthorized('Token is required.');
        }
        
        if (!$tokenType) {
            return response()->unauthorized('Token type is required.');
        }
        
        // Use appropriate token model based on token type
        if ($tokenType === 'login_token' || $tokenType === 'signup_verification_token') {
            $tokenRecord = SessionToken::findValidToken($token, $tokenType);
        } elseif ($tokenType === 'forgot_password_token') {
            $tokenRecord = ForgetToken::findValidToken($token, $tokenType);
        } else {
            return response()->unauthorized('Invalid token type.');
        }
            
        if (!$tokenRecord) {
            return response()->unauthorized('Invalid or expired token.');
        }

        // Debug: Check what we have in tokenRecord
        if (!is_object($tokenRecord)) {
            return response()->unauthorized('Invalid token record format.');
        }

        $user = User::find((string) data_get($tokenRecord, 'user_id'));
        
        if (!$user) {
            return response()->notFound('User not found.');
        }

        // Add the real user to request and set user resolver
        $request->merge([
            'token_record' => $tokenRecord,
            'verified_user' => $user,
            'user' => $user
        ]);

        // Set user resolver for framework methods
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
