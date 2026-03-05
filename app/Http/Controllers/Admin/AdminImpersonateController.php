<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImpersonateReadRequest;
use App\Http\Resources\UserResource;

class AdminImpersonateController extends Controller
{
    public function read(ImpersonateReadRequest $request)
    {
        $user = $request->validatedUser();

        
        session(['impersonated_user_id' => $user->_id]);

        return response()->json([
            'success' => true,
            'message' => 'User impersonation started successfully!',
            'data' => UserResource::make($user),
        ]);
    }

    public function stop()
    {
        session()->forget('impersonated_user_id');

        return response()->json([
            'success' => true,
            'message' => 'Impersonation stopped successfully!',
        ]);
    }
}
