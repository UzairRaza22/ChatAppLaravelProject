<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;

// Sab se pehle Token aur Active status check hoga
Route::prefix('team')->middleware(['check.token:login_token', 'check.active'])->group(function () {

    // 1. Create Team
    // Pehle validation hogi, phir name check hoga, phir workspace creator check hoga
    Route::post('/create', [TeamController::class, 'create'])
        ->middleware([
            'check.validation:CreateTeamRequest', 
            'team.unique.name',
            'workspace.creator.team'
        ]);

    // 2. List Teams (Ismein workspace_id body ya query mein aayegi)
    Route::get('/list', [TeamController::class, 'index'])
        ->middleware([
            'check.validation:ListTeamRequest',
            'teams.exist'
        ]);

    // 3. Team Specific Actions (Update, Delete, Members)
    // In sab ke liye pehle Team ka hona aur user ka uska Creator hona zaroori hai
    Route::middleware(['team.exists', 'workspace.creator.team'])->group(function () {
        
        Route::put('/update', [TeamController::class, 'update'])
            ->middleware('check.validation:UpdateTeamRequest');

        Route::delete('/delete', [TeamController::class, 'delete'])
            ->middleware('check.validation:DeleteTeamRequest');

        // Member Management
        Route::post('/add-member', [TeamController::class, 'addMember'])
            ->middleware([
                'check.validation:AddTeamMemberRequest',
                'workspace.member.team',   // Pehle workspace ka member ho
                'team.member.exists'      // Phir check karein team mein pehle se toh nahi
            ]);

        Route::post('/remove-member', [TeamController::class, 'removeMember'])
            ->middleware('check.validation:RemoveTeamMemberRequest');
    });
});