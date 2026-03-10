<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageFileMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->error('File path is required.', 400);
        }

        if (!Storage::disk('gridfs')->exists($path)) {
            return response()->notFound('File not found.');
        }

        $request->merge([
            'file_path' => $path,
            'file_name' => basename($path),
        ]);

        return $next($request);
    }
}
