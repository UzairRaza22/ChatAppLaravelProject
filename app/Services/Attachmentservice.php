<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    /*
    |--------------------------------------------------------------------------
    | GridFS Disk Helper
    |--------------------------------------------------------------------------
    */
    private function disk()
    {
        return Storage::disk('gridfs');
    }

    /*
    |--------------------------------------------------------------------------
    | Upload
    | Stores the file in GridFS at: workspaces/{workspace_id}/messages/{filename}
    | Also stores a .meta.json sidecar with original_filename, size, mimetype.
    | Returns an array of file metadata ready to persist to MongoDB.
    |--------------------------------------------------------------------------
    */
    public function upload(UploadedFile $file, string $workspaceId): array
    {
        $fileName = $file->getClientOriginalName();
        $fileMime = $file->getMimeType();
        $fileSize = $file->getSize();
        $filePath = 'workspaces/' . $workspaceId . '/messages/' . $fileName;
        $isAudio  = str_starts_with($fileMime, 'audio/');

        // Store file in GridFS
        $this->disk()->put($filePath, file_get_contents($file->getRealPath()));

        // Store metadata sidecar alongside the file
        $this->disk()->put($filePath . '.meta.json', json_encode([
            'original_filename' => $fileName,
            'size'              => $fileSize,
            'mimetype'          => $fileMime,
        ]));

        return [
            'file_path'    => $filePath,
            'file_name'    => $fileName,
            'file_mime'    => $fileMime,
            'message_type' => $isAudio ? 'voice' : 'file',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Stream / Download
    | Streams file contents from GridFS.
    | Reads mimetype from .meta.json sidecar if available.
    | inline  → images, video, PDF  (browser preview)
    | attachment → everything else  (forced download)
    |--------------------------------------------------------------------------
    */
    public function stream(string $filePath): \Illuminate\Http\Response
    {
        $contents = $this->disk()->get($filePath);

        $meta     = $this->disk()->exists($filePath . '.meta.json')
            ? json_decode($this->disk()->get($filePath . '.meta.json'), true)
            : [];

        $fileName = $meta['original_filename'] ?? basename($filePath);
        $mimeType = $meta['mimetype']           ?? 'application/octet-stream';

        $disposition = str_starts_with($mimeType, 'image/')
            || str_starts_with($mimeType, 'video/')
            || $mimeType === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return response($contents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', "{$disposition}; filename=\"{$fileName}\"");
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    | Removes the file and its .meta.json sidecar from GridFS.
    | Skips silently if the file does not exist — no crash on missing file.
    |--------------------------------------------------------------------------
    */
    public function delete(string $filePath): void
    {
        $this->disk()->exists($filePath)
            && $this->disk()->delete($filePath);

        $this->disk()->exists($filePath . '.meta.json')
            && $this->disk()->delete($filePath . '.meta.json');
    }

    /*
    |--------------------------------------------------------------------------
    | Format File Size  (utility)
    |--------------------------------------------------------------------------
    */
    public function formatSize(int $bytes): string
    {
        return $bytes >= 1024 * 1024
            ? round($bytes / (1024 * 1024), 2) . ' MB'
            : ($bytes >= 1024
                ? round($bytes / 1024, 2) . ' KB'
                : $bytes . ' bytes');
    }
}
