<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalActivityLoggerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->storeLog($request, $response);

        return $response;
    }

    /**
     * Store activity details in MongoDB.
     */
    protected function storeLog(Request $request, $response): void
    {
        // 1. Sensitive data ko filter karna (Security Best Practice)
        $excludedFields = ['password', 'password_confirmation', 'token', 'access_token'];
        $payload = $request->except($excludedFields);

        // 2. Route information ko handle karna (Null-Safety)
        $routeName = $request->route() ? $request->route()->getName() : $request->path();

        // 3. Activity Create (Clean & Direct)
        Activity::create([
            'user_id'         => Auth::id() ?? 'Guest',
            'ip_address'      => $request->ip(),
            'url'             => $request->fullUrl(),
            'method'          => $request->method(),
            'action'          => $routeName,
            'request_payload' => $payload,
            'status_code'     => $response->getStatusCode(),
            'user_agent'      => $request->userAgent(),
            'created_at'      => now(),
        ]);
    }
}