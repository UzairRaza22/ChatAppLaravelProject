<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChannelReadRequest;
use App\Http\Resources\ChannelResource;

class AdminChannelController extends Controller
{
    public function read(ChannelReadRequest $request)
    {
        $channels = $request->validatedChannel();

        return response()->json([
            'success' => true,
            'message' => 'Channel(s) retrieved successfully!',
            'data' => $channels instanceof \Illuminate\Database\Eloquent\Collection
                ? ChannelResource::collection($channels)
                : ChannelResource::make($channels),
        ]);
    }
}
