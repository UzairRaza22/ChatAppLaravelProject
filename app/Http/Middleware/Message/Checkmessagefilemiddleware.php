<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageFileMiddleware
{
    /**
     * Validates the file path exists in GridFS before download.
     * Merges file_path and file_name into request attributes.
     *
     * Query param: path
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->query('path');

        if (!$path) {
            return response()->error('File path is required.', 400);
        }

        if (!Storage::disk('gridfs')->exists($path)) {
            return response()->notFound('File not found.');
        }

        $request->attributes->set('file_path', $path);
        $request->attributes->set('file_name', basename($path));

        return $next($request);
    }
}
