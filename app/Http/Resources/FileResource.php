<?php

namespace App\Http\Resources;

class FileResource extends BaseResource
{
    /**
     * Transform the resource data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function transformData($request)
    {
        return [
            'id' => (string) $this->_id,
            'file_id' => $this->file_id ? (string) $this->file_id : null,
            'filename' => $this->filename,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'metadata' => $this->metadata ?? [],
            'upload_date' => $this->upload_date,
            'download_url' => $this->when(isset($this->file_id), route('files.download', $this->file_id)),
            'metadata_url' => $this->when(isset($this->file_id), route('files.metadata', $this->file_id)),
            'uploaded_by' => $this->when($this->relationLoaded('uploadedBy'), new UserResource($this->uploadedBy)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
