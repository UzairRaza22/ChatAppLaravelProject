<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;

Route::middleware(['check.token:login_token', 'check.active'])->group(function () {

    // Create team
    Route::post('/create', [TeamController::class, 'create'])
        ->middleware([
            'check.validation:create_team_request', 
            'check.team.unique.name',
            'check.workspace.creator.team'
        ]);

    // Read teams
    Route::get('/read', [TeamController::class, 'read'])
        ->middleware([
            'check.validation:read_team_request',
            'check.teams.exist'
        ]);

    Route::middleware(['check.team.exists', 'check.workspace.creator.team'])->group(function () {
        
        // update teams
        Route::put('/update', [TeamController::class, 'update'])
            ->middleware([
                'check.validation:update_team_request',
                'check.team.unique.name' 
            ]);
         // delete teams
        Route::delete('/delete', [TeamController::class, 'delete'])
            ->middleware('check.validation:delete_team_request');
         // add member
            Route::post('/add-member', [TeamController::class, 'addMember'])
            ->middleware([
                'check.validation:add_team_member_request',
                'check.workspace.member.team', 
                'check.team.member.exists'
            ]);
            // remove member
        Route::post('/remove-member', [TeamController::class, 'removeMember'])
            ->middleware('check.validation:remove_team_member_request');

    });
});