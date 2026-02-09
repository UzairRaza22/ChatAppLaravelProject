<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use MongoDB\GridFS\Bucket;

class GridFSService
{
    private $bucket;

    public function __construct()
    {
        // Use Laravel's built-in MongoDB connection
        $connection = DB::connection('mongodb');
        $manager = $connection->getMongoClient();
        $database = $manager->selectDatabase($connection->getConfig('database'));
        
        $this->bucket = $database->selectGridFSBucket([
            'bucketName' => 'files'
        ]);
    }

    public function uploadFile(UploadedFile $file, string $directory = 'messages')
    {
        $filename = $directory . '/' . Str::uuid() . '_' . $file->getClientOriginalName();
        $stream = fopen($file->getPathname(), 'rb');
        
        $fileId = $this->bucket->uploadFromStream(
            $filename,
            $stream,
            [
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'directory' => $directory,
                    'uploaded_at' => now()->toISOString()
                ]
            ]
        );

        fclose($stream);

        return [
            'file_id' => $fileId->__toString(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ];
    }

    public function downloadFile(string $fileId)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($fileId);
            $stream = $this->bucket->openDownloadStream($objectId);
            
            $fileDocument = $this->bucket->find(['_id' => $objectId])->toArray();
            
            if (empty($fileDocument)) {
                return null;
            }

            return [
                'stream' => $stream,
                'filename' => $fileDocument[0]['filename'],
                'metadata' => $fileDocument[0]['metadata'] ?? []
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function deleteFile(string $fileId)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($fileId);
            $this->bucket->delete($objectId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getFileMetadata(string $fileId)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($fileId);
            $fileDocument = $this->bucket->find(['_id' => $objectId])->toArray();
            
            if (empty($fileDocument)) {
                return null;
            }

            return $fileDocument[0];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFileUrl(string $fileId)
    {
        return route('files.download', $fileId);
    }
}
