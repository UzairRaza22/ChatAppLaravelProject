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

    /*
    |--------------------------------------------------------------------------
    | NEW: List Available Members
    | Get all verified users that can be added to workspace
    | Excludes currently added members
    |--------------------------------------------------------------------------
    */
    Route::get('/{id}/available-members', [WorkspaceController::class, 'listAvailableMembers'])->middleware([
        'check.workspace.exists',
    ]);

    /*
    |--------------------------------------------------------------------------
    | NEW: Search Members by Name or Email
    | Quick user search (minimum 2 characters)
    | Query: /workspaces/{id}/search-members?query=john
    |--------------------------------------------------------------------------
    */
    Route::get('/{id}/search-members', [WorkspaceController::class, 'searchMembers'])->middleware([
        'check.workspace.exists',
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
