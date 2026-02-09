<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use App\Http\Resources\FileResource;

class File extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'files';

    protected $fillable = [
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'workspace_id',
        'channel_id',
        'uploaded_by',
        'is_public',
        'gridfs_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'uploaded_at',
    ];

    /**
     * Get the user who uploaded the file
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the workspace that owns the file
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Get the channel that owns the file
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
