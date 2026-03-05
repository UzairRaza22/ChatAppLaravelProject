<?php

namespace App\Http\Middleware\auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->notFound('User not found.');
        }
        
        if (!$user->is_active) {
            return response()->forbidden('Activate your account!');
        }
        
        return $next($request);
    }
}
