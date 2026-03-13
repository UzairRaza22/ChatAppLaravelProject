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
            'fcm_token' => 'required|string',
            'platform' => 'nullable|in:web,android,ios',
        ]);

        $user = $request->user();

        $fcmToken = FcmToken::updateOrCreate(
            ['token' => $request->input('fcm_token')],
            [
                'user_id' => (string) data_get($user, '_id'),
                'platform' => $request->input('platform', 'web'),
                'last_seen_at' => Carbon::now(),
            ]
        );

        return response()->success(null, 'FCM token stored successfully.');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();

        FcmToken::where('token', $request->input('fcm_token'))
            ->where('user_id', (string) data_get($user, '_id'))
            ->delete();

        return response()->success(null, 'FCM token removed successfully.');
    }
}
