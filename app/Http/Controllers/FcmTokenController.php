<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FcmTokenController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|in:web,android,ios',
        ]);

        $user = $request->user();

        $fcmToken = FcmToken::updateOrCreate(
            ['token' => data_get($request, 'token')],
            [
                'user_id' => data_get($user, '_id'),
                'platform' => data_get($request, 'platform', 'web'),
                'last_seen_at' => Carbon::now(),
            ]
        );

        return response()->success(null, 'FCM token stored successfully.');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();

        FcmToken::where('token', data_get($request, 'token'))
            ->where('user_id', data_get($user, '_id'))
            ->delete();

        return response()->success(null, 'FCM token removed successfully.');
    }
}
