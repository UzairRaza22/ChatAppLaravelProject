<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageFileMiddleware
{
    /**
     * Validate the file path exists in storage before download.
     * Merges resolved file_path and full_path into the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'File path is required.'
            ], 400);
        }

        if (!Storage::disk('public')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.'
            ], 404);
        }

        $request->merge([
            'file_path'  => $path,
            'full_path'  => Storage::disk('public')->path($path),
            'file_name'  => basename($path),
        ]);

        return $next($request);
    }
}
