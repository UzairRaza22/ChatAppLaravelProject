<?php

namespace App\Http\Middleware\auth;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class CheckUserExistForForgotMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = data_get($request, 'email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->notFound('User not found.');
        }

        // Add user to request for controller use
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
