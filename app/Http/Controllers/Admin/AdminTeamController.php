<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamReadRequest;
use App\Http\Resources\TeamResource;

class AdminTeamController extends Controller
{
    public function read(TeamReadRequest $request)
    {
        $teams = $request->validatedTeam();

        return response()->json([
            'success' => true,
            'message' => 'Team(s) retrieved successfully!',
            'data' => $teams instanceof \Illuminate\Database\Eloquent\Collection
                ? TeamResource::collection($teams)
                : TeamResource::make($teams),
        ]);
    }
}
