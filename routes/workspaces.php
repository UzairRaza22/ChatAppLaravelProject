<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;
//use App\Http\Controllers\TeamController;

Route::middleware('check.token:login_token')->group(function () {

    //create workspace
    Route::post('/create', [WorkspaceController::class, 'create'])->middleware([
        'check.validation:CreateWorkspaceRequest',
        'workspace.unique.name'
    ]);

    //read workspaces
    Route::get('/read', [WorkspaceController::class, 'get'])->middleware(
        'check.workspaces.exist'
    );


    //update workspace
    Route::patch('/update', [WorkspaceController::class, 'update'])->middleware([
        'check.validation:UpdateWorkspaceRequest',
        'workspace.creator',
        'workspace.unique.name'
    ]);

    //delete workspace
    Route::delete('/delete', [WorkspaceController::class, 'delete'])->middleware([
        'check.workspace.exists',
        'check.workspace.creator',
    ]);

    //add members
    Route::post('/add-members', [WorkspaceController::class, 'addMembers'])->middleware([
        'workspace.exists',
        'workspace.creator',
        'check.validation:AddWorkspaceMemberRequest'
    ]);

    //remove members
    Route::delete('/remove-members', [WorkspaceController::class, 'removeMembers'])->middleware([
        'workspace.exists',
        'workspace.creator',
        'check.validation:RemoveWorkspaceMemberRequest'
    ]);
});
