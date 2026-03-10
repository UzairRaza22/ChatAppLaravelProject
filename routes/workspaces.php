<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkspaceController;

Route::middleware('check.token:login_token')->group(function () {

    //create workspace
    Route::post('/create', [WorkspaceController::class, 'create'])->middleware([
        'check.validation:create_workspace_request',
        'check.workspace.unique.name'
    ]);

    //read workspaces
    Route::get('/read', [WorkspaceController::class, 'read'])->middleware(
        'check.workspaces.exist'
    );
    
    //read specific workspace
    Route::get('/read/{id}', [WorkspaceController::class, 'read'])->middleware(
        'check.workspaces.exist'
    );

    //update workspace
    Route::patch('/update', [WorkspaceController::class, 'update'])->middleware([
        'check.validation:update_workspace_request',
        'check.workspaces.exist',
        'check.workspace.creator',
        'check.workspace.unique.name'
    ]);

    //delete workspace
    Route::delete('/delete', [WorkspaceController::class, 'delete'])->middleware([
        'check.workspace.exists',
        'check.workspace.creator',
    ]);

    //add members
    Route::post('/add-members', [WorkspaceController::class, 'addMembers'])->middleware([
        'check.validation:add_workspace_member_request',
        'check.workspace.exists',
        'check.workspace.creator'
    ]);

    //remove members
    Route::delete('/remove-members', [WorkspaceController::class, 'removeMembers'])->middleware([
        'check.validation:remove_workspace_member_request',
        'check.workspace.exists',
        'check.workspace.creator',
        'check.members.exist'
       
    ]);
});
