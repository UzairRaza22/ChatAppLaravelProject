<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response; // Macro use karne ke liye zaroori hai

class SocialAuthController extends Controller
{
    /**
     * User ko Google login page par redirect karein.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Google se wapis aane ke baad data handle karein.
     */
    public function handleGoogleCallback(): JsonResponse
    {
        // 1. Google User fetch karein
        $googleUser = Socialite::driver('google')->stateless()->user();

        // 2. Custom Token Generate karein
        $customToken = Str::random(60);

        // 3. User Create ya Update (Custom access_token ke sath)
        $user = User::updateOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'name'         => $googleUser->getName(),
            'google_id'    => $googleUser->getId(),
            'avatar'       => $googleUser->getAvatar(),
            'access_token' => $customToken, // Aapke Model mein jo field hai
            'password'     => null,
        ]);

        // 4. Aapke Macro (ResponseServiceProvider) ke mutabiq response
        // Note: Humne yahan 'response()->success()' use kiya hai jaisa aapne boot() mein define kiya
        return response()->success([
            'user'  => $user,
            'token' => $customToken,
        ], 'Login Successful');
    }
}