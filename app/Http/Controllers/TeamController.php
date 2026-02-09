<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessResource;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function readAll(Request $request)
    {
        $teams = Team::with(['members', 'channels', 'workspace'])
            ->whereHas('members', function($query) use ($request) {
                $query->where('user_id', $request->user()->_id);
            })
            ->paginate(20);

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team.
     */
    public function create(CreateTeamRequest $request)
    {
        $workspace = $request->user()->workspaces()->findOrFail($request->workspace_id);

        $team = Team::create([
            'name' => $request->name,
            'description' => $request->description,
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()->id,
        ]);

        $team->members()->attach($request->user()->id, ['role' => 'admin']);

        return new TeamResource($team->load(['members', 'channels', 'workspace']));
    }

    /**
     * Display the specified team.
     */
    public function read(Request $request, $id)
    {
        $team = $request->user()->teams()
            ->with(['members', 'channels', 'workspace'])
            ->findOrFail($id);

        return new TeamResource($team);
    }

    /**
     * Update the specified team.
     */
    public function update(UpdateTeamRequest $request, $id)
    {
        $team = $request->user()->teams()->findOrFail($id);
        $team->update($request->validated());

        return new TeamResource($team->load(['members', 'channels', 'workspace']));
    }

    /**
     * Remove the specified team.
     */
    public function delete(Request $request, $id)
    {
        $team = $request->user()->teams()->findOrFail($id);
        $team->delete();

        return new SuccessResource(['message' => 'Team deleted successfully']);
    }
}
