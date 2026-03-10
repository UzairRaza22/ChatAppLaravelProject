<?php

namespace App\Http\Middleware\auth;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserExistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = data_get($request, 'email');
        
        $user = User::where('email', $email)->first();
        
        if ($user) {
            return response()->error('User already exists', 409);
        }

        return $next($request);
    }
}
