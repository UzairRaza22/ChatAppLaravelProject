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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $tokenType = null): Response
    {
        // For signup verification, check if email exists first
        if ($tokenType === 'signup_verification_token') {
            $email = $request->email;
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Email not registered.'
                ], 404);
            }
        }

        // Get token from route, header, or request body
        $token = $request->route('token') ?? 
                 $request->headers->get('authorization') ?? 
                 $request->input('token');
        
        // Remove 'Bearer ' prefix if present
        $token = str_replace('Bearer ', '', $token);
        
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

        // Check if tokenRecord has user_id before accessing it
        if (!isset($tokenRecord->user_id) || empty($tokenRecord->user_id)) {
            return response()->unauthorized('Invalid token format.');
        }

        $user = User::find((string) $tokenRecord->user_id);
        
        if (!$user) {
            return response()->notFound('User not found.');
        }

        $request->merge([
            'token_record' => $tokenRecord,
            'verified_user' => $user
        ]);

        // Only set user resolver if user is not already set
        if (!$request->user()) {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        return $next($request);
    }
}
