<?php

namespace App\Http\Middleware\auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use App\Models\User;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CheckCredentialsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = data_get($request, 'email');
        $password = data_get($request, 'password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->notFound('Email not registered.');
        }

        if (!Hash::check($password, data_get($user, 'password'))) {
            return response()->unauthorized('Invalid credentials');
        }

        // Add user to request data for controller access
        $request->merge(['user' => $user]);
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
