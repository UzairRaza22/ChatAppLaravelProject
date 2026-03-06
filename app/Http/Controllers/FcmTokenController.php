<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'platform' => 'nullable|in:web,android,ios',
        ]);

        $user = $request->user();

        $fcmToken = FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->_id,
                'platform' => $request->platform ?? 'web',
                'last_seen_at' => Carbon::now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token stored successfully.',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user();

        FcmToken::where('token', $request->token)
            ->where('user_id', $user->_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully.',
        ]);
    }
}
