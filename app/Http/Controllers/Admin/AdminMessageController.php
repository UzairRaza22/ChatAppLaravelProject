<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MessageReadRequest;
use App\Http\Resources\MessageResource;

class AdminMessageController extends Controller
{
    public function read(MessageReadRequest $request)
    {
        $messages = $request->validatedMessage();

        return response()->json([
            'success' => true,
            'message' => 'Message(s) retrieved successfully!',
            'data' => $messages instanceof \Illuminate\Database\Eloquent\Collection
                ? MessageResource::collection($messages)
                : MessageResource::make($messages),
        ]);
    }
}
