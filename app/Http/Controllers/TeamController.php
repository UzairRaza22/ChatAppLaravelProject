<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class TeamController extends Controller
{
    private function sendEvent($event)
    {
        Http::post('http://localhost:3000/event', $event);
    }
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
        //eent
        $event = [
            'eventName' => 'team_created',
            'module' => 'team',
            'operation' => 'create',
            'referenceId' => $team->_id ?? $team->id,
            'userIds' => $team->members,
            'metadata' => [
                'team' => new TeamResource($team)
            ]
        ];

        $this->sendEvent($event);
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
    //eent
     $event = [
            'eventName' => 'team_updated',
            'module' => 'team',
            'operation' => 'update',
            'referenceId' => $team->_id ?? $team->id,
            'userIds' => $team->members,
            'metadata' => [
                'team' => new TeamResource($team)
            ]
        ];

        $this->sendEvent($event);
        return response()->success(new TeamResource($team), 'Team updated successfully');
    }

     // 4. Add Member to Team 
     
    public function addMember(Request $request)
    {
        $team = data_get($request, 'team');
        $memberIds = data_get($request, 'member_ids', []); 

        $team->push('members', $memberIds, true);
//eent
 $event = [
            'eventName' => 'team_member_added',
            'module' => 'team',
            'operation' => 'member_added',
            'referenceId' => $team->_id ?? $team->id,
            'userIds' => $team->members,
            'metadata' => [
                'team' => new TeamResource($team),
                'teamId' => (string) ($team->_id ?? $team->id),
                'workspaceId' => $team->workspace_id,
                'addedUserIds' => $memberIds
            ]
        ];

        $this->sendEvent($event);
        return response()->success(new TeamResource($team), 'Members added to team successfully');
    }

    
    // 5. Remove Member from Team 
    
    public function removeMember(Request $request)
    {
        $team = data_get($request, 'team');  
        $userIds = data_get($request, 'member_ids', []);

        $team->pull('members', $userIds);
    //eent
        $event = [
            'eventName' => 'team_member_removed',
            'module' => 'team',
            'operation' => 'member_removed',
            'referenceId' => $team->_id ?? $team->id,
            'userIds' => $team->members,
            'metadata' => [
                'team' => new TeamResource($team),
                'teamId' => (string) ($team->_id ?? $team->id),
                'workspaceId' => $team->workspace_id,
                'removedUserIds' => $userIds
            ]
        ];

        $this->sendEvent($event);
        return response()->success(new TeamResource($team), 'Members removed from team successfully');
    }

    
    // 6. Delete Team

    public function delete(Request $request)
    {
        $team = data_get($request, 'team');
         $teamId = $team->_id ?? $team->id;
         $memberIds = $team->members ?? [];
        $team->delete();
    //eent
     $event = [
        'eventName' => 'team_deleted',
        'module' => 'team',
        'operation' => 'delete',
        'referenceId' => (string) $teamId,
        'userIds' => $memberIds,
        'metadata' => [
            'teamId' => (string) $teamId
        ]
    ];

    $this->sendEvent($event);
        return response()->success(null, 'Team deleted successfully');
    }
}