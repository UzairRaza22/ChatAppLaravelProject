<?php

namespace App\Http\Middleware\Message;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageFileUploadMiddleware
{
    /**
     * Handles GridFS file upload for create and update.
     * Skips silently if no file is present in the request.
     *
     * On update:
     *   - Deletes the old file from GridFS if one exists on the message.
     *
     * Merges into request attributes:
     *   - file_path
     *   - file_name
     *   - file_mime
     *   - resolved_message_type  ('file' | 'text')
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No file — pass through, controller will use text-only defaults
        if (!$request->hasFile('file')) {
            return $next($request);
        }

        $file    = $request->file('file');
        $channel = $request->attributes->get('channel');

        // Build storage path: workspaces/{workspace_id}/messages/{original_filename}
        $workspaceId = (string) $channel->workspace_id;
        $fileName    = $file->getClientOriginalName();
        $filePath    = "workspaces/{$workspaceId}/messages/{$fileName}";

        // On update: delete the old GridFS file if present
        $existingMessage = $request->attributes->get('message');

        $hasOldFile = $existingMessage
            && $existingMessage->file_path
            && Storage::disk('gridfs')->exists($existingMessage->file_path);

        $hasOldFile && Storage::disk('gridfs')->delete($existingMessage->file_path);

        // Store new file in GridFS
        Storage::disk('gridfs')->put($filePath, file_get_contents($file->getRealPath()));

        $request->attributes->set('file_path', $filePath);
        $request->attributes->set('file_name', $fileName);
        $request->attributes->set('file_mime', $file->getMimeType());
        $request->attributes->set('resolved_message_type', 'file');

        return $next($request);
    }
}
