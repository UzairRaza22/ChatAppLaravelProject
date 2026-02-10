<?php

namespace App\Http\Resources;

class FileResource extends BaseResource
{
    /**
     * Transform resource data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'id' => (string) $this->_id,
            'file_id' => (string) $this->_id,
            'filename' => $this->filename,
            'original_name' => $this->original_name ?? $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'metadata' => $this->metadata ?? [],
            'upload_date' => $this->upload_date ?? $this->created_at,
            'download_url' => route('files.download', $this->_id),
            'metadata_url' => route('files.read', $this->_id),
            'uploaded_by' => $this->when($this->relationLoaded('uploadedBy'), new UserResource($this->uploadedBy)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
