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
            return response()->json(['message' => 'User not found'], 401);
        }

        $team = Team::create([
            'workspace_id' => $request->workspace_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'creator_id'   => $user->_id,
            'members'      => [$user->_id] // Creator auto-member
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data'    => $team
        ], 201);
    }

    // 2. List Teams
    public function index(Request $request)
    {
        $teams = $request->teams ?? Team::where('workspace_id', $request->workspace_id)->get();

        return response()->json([
            'success' => true,
            'data'    => $teams
        ], 200);
    }

    // 3. Update Team
    public function update(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);

        $team->update([
            'name'        => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'data'    => $team
        ], 200);
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

        return response()->json([
            'success' => true,
            'message' => 'Members added to team successfully'
        ], 200);
    }

    // 5. Remove Member from Team
    public function removeMember(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);

        $team->pull('members', $request->member_id);

        return response()->json([
            'success' => true,
            'message' => 'Member removed from team successfully'
        ], 200);
    }

    // 6. Delete Team
    public function delete(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);
        
        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully'
        ], 200);
    }
}