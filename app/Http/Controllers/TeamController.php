<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // 1. Create Team
    public function create(Request $request)
    {
        $user = $request->user();

        $team = Team::create([
            'workspace_id' => data_get($request, 'workspace_id'),
            'name'         => data_get($request, 'name'),
            'description'  => data_get($request, 'description'),
            'creator_id'   => data_get($user, '_id'),
            'members'      => [(string) data_get($user, '_id')] 
        ]);

        return response()->success(new TeamResource($team), 'Team created successfully');
    }
    
    // 2. Read Teams

    public function read(Request $request)
    {
        $teams = data_get($request, 'teams'); 
        return response()->success(TeamResource::collection($teams), 'Teams retrieved successfully');
    }

     // 3. Update Team
    
    public function update(Request $request)
    {
        $team = data_get($request, 'team');

        $team->update([
            'name'         => data_get($request, 'name'),
            'description'  => data_get($request, 'description')
        ]);

        return response()->success(new TeamResource($team), 'Team updated successfully');
    }

     // 4. Add Member to Team 
     
    public function addMember(Request $request)
    {
        $team = data_get($request, 'team');
        $memberIds = data_get($request, 'member_ids', []); 

        $team->push('members', $memberIds, true);

        return response()->success(new TeamResource($team), 'Members added to team successfully');
    }

    
    // 5. Remove Member from Team 
    
    public function removeMember(Request $request)
    {
        $team = data_get($request, 'team');  
        $userIds = data_get($request, 'member_ids', []);

        $team->pull('members', $userIds);

        return response()->success(new TeamResource($team), 'Members removed from team successfully');
    }

    
    // 6. Delete Team

    public function delete(Request $request)
    {
        $team = data_get($request, 'team');
        $team->delete();

        return response()->success(null, 'Team deleted successfully');
    }
}