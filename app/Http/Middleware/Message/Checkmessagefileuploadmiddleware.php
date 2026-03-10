<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageFileUploadMiddleware
{
    /**
     * Handles all file upload logic for create and update.
     * Moves file to GridFS and merges file data into request.
     * Skipped automatically if no file is present.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No file — skip
        if (!$request->hasFile('file')) {
            return $next($request);
        }

        $workspace = data_get($request, 'workspace');
        $message   = data_get($request, 'message'); // only present on update

        $file       = $request->file('file');
        $fileName   = $file->getClientOriginalName();
        $gridfsPath = "workspaces/{$workspace->_id}/messages/{$fileName}";

        // On update — delete old file from GridFS first
        if ($message && $message->file_path && Storage::disk('gridfs')->exists($message->file_path)) {
            Storage::disk('gridfs')->delete($message->file_path);
        }

        // Store new file in GridFS
        Storage::disk('gridfs')->put(
            $gridfsPath,
            file_get_contents($file->getRealPath())
        );

        // Resolve message_type: if content also exists → text, else → file
        $resolvedType = $request->filled('content') ? 'text' : 'file';

        $request->merge([
            'file_path'             => $gridfsPath,
            'file_name'             => $fileName,
            'file_mime'             => $file->getMimeType(),
            'resolved_message_type' => $resolvedType,
        ]);

        return $next($request);
    }
}
