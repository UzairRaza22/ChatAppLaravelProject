<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Workspace;
use App\Models\Team;
use App\Models\Channel;
use App\Services\GridFSService;
use App\Http\Requests\File\CreateFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $gridFSService;

    public function __construct(GridFSService $gridFSService)
    {
        $this->gridFSService = $gridFSService;
    }

    /**
     * Display a listing of files.
     */
    public function readAll(Request $request)
    {
        $files = File::with(['uploadedBy'])
            ->where('uploaded_by', $request->user()->_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return FileResource::collection($files);
    }

    /**
     * Store a newly uploaded file.
     */
    public function create(CreateFileRequest $request)
    {
        $file = $request->file('file');
        $workspaceId = $request->workspace_id;
        $teamId = $request->team_id;
        $channelId = $request->channel_id;

        // Validate workspace exists
        $workspace = Workspace::find($workspaceId);
        if (!$workspace) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect workspace ID'
            ], 404);
        }

        // Validate team belongs to workspace
        $team = Team::where('id', $teamId)
            ->where('workspace_id', $workspace->id)
            ->first();
        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect team ID or team does not belong to this workspace'
            ], 404);
        }

        // Validate channel belongs to team
        $channel = Channel::where('id', $channelId)
            ->where('workspace_id', $workspace->id)
            ->where('team_id', $team->id)
            ->first();
        if (!$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect channel ID or channel does not belong to this team'
            ], 404);
        }

        // Check if user has access to this channel
        if ($channel->type !== 'public' && !in_array($request->user()->_id, $channel->user_ids ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this channel'
            ], 403);
        }

        $gridFSId = $this->gridFSService->uploadFile($file);

        $fileRecord = File::create([
            'filename' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'gridfs_id' => $gridFSId,
            'uploaded_by' => $request->user()->id,
            'workspace_id' => $workspace->id,
            'team_id' => $team->id,
            'channel_id' => $channel->id,
        ]);

        return new FileResource($fileRecord->load(['uploadedBy']));
    }

    /**
     * Display the specified file metadata.
     */
    public function read(Request $request, $id)
    {
        $file = $request->user()->files()
            ->with(['uploadedBy'])
            ->findOrFail($id);

        return new FileResource($file);
    }

    /**
     * Update file metadata.
     */
    public function update(UpdateFileRequest $request, $id)
    {
        $file = $request->user()->files()->findOrFail($id);
        
        $file->update([
            'filename' => $request->filename ?? $file->filename,
            'original_name' => $request->original_name ?? $file->original_name,
            'metadata' => $request->metadata ?? $file->metadata,
        ]);

        return new FileResource($file->load(['uploadedBy']));
    }

    /**
     * Download the specified file.
     */
    public function download(Request $request, $id)
    {
        $file = $request->user()->files()->findOrFail($id);
        $fileStream = $this->gridFSService->downloadFile($file->gridfs_id);

        return response($fileStream)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->original_name . '"')
            ->header('Content-Length', $file->size);
    }

    /**
     * Remove the specified file.
     */
    public function delete(Request $request, $id)
    {
        $file = $request->user()->files()->findOrFail($id);
        $this->gridFSService->deleteFile($file->gridfs_id);
        $file->delete();

        return new SuccessResource(['message' => 'File deleted successfully']);
    }
}
