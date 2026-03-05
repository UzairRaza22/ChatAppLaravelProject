<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserReadRequest;
use App\Http\Resources\UserResource;

class AdminUserController extends Controller
{
    public function read(UserReadRequest $request)
    {
        $users = $request->validatedUser();

        return response()->json([
            'success' => true,
            'message' => 'User(s) retrieved successfully!',
            'data' => $users instanceof \Illuminate\Database\Eloquent\Collection
                ? UserResource::collection($users)
                : UserResource::make($users),
        ]);
    }
}
