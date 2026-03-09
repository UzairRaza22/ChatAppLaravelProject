<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // 1. Create Team
    public function create(Request $request)
    {
        // Uzair's style: User request se nikalna
        $user = $request->user();

        if (!$user) {
            return response()->unauthorized('User not found');
        }

        $team = Team::create([
            'workspace_id' => $request->workspace_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'creator_id'   => $user->_id,
            'members'      => [$user->_id] // Creator auto-member
        ]);

        return response()->success($team, 'Team created successfully', 201);
    }

    // 2. List Teams
    public function index(Request $request)
    {
        $teams = $request->teams ?? Team::where('workspace_id', $request->workspace_id)->get();

        return response()->success($teams, 'Teams retrieved successfully');
    }

    // 3. Update Team
    public function update(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);

        $team->update([
            'name'        => $request->name,
            'description' => $request->description
        ]);

        return response()->success($team, 'Team updated successfully');
    }

    // 4. Add Member to Team (Updated for Multiple Emails/IDs)
    public function addMember(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);
        
        // Middleware se aane wali array IDs
        $memberIds = $request->member_ids; 

        // Har ID ko loop ke zariye members array mein add karna
        if (!empty($memberIds)) {
            foreach ($memberIds as $id) {
                // 'true' parameter ensures uniqueness in MongoDB array
                $team->push('members', $id, true);
            }
        }

        return response()->success(null, 'Members added to team successfully');
    }

    // 5. Remove Member from Team
    public function removeMember(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);

        $team->pull('members', $request->member_id);

        return response()->success(null, 'Member removed from team successfully');
    }

    // 6. Delete Team
    public function delete(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);
        
        $team->delete();

        return response()->success(null, 'Team deleted successfully');
    }
}