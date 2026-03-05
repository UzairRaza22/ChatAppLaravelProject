<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // 1. Create Team
    public function create(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $team = Team::create([
            'workspace_id' => $request->workspace_id,
            'name'         => $request->name,
            'description'  => $request->description,
            'creator_id'   => $user->_id,
            'members'      => [$user->_id]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data'    => $team
        ], 201);
    }

    // 2. Read Teams (CRUD standard)
    public function read(Request $request)
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

    // 4. Add Member to Team (Using member_ids from Middleware)
    public function addMember(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);
        $memberIds = $request->member_ids; 

        if (!empty($memberIds)) {
            foreach ($memberIds as $id) {
                $team->push('members', $id, true);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Members added to team successfully'
        ], 200);
    }

    // 5. Remove Member from Team (Updated for Emails Array)
    public function removeMember(Request $request)
    {
        $team = $request->team ?? Team::find($request->team_id);
        $emails = $request->emails; // Request se emails array uthaya

        if (!empty($emails)) {
            foreach ($emails as $email) {
                // Email se user find karke uski ID pull karna
                $user = User::where('email', $email)->first();
                if ($user) {
                    $team->pull('members', strval($user->_id));
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Specified members removed from team successfully'
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