<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\{SessionToken, ForgetToken, User};
use App\Mail\SignupVerificationEmail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $user = User::add($request);

        $workspace = $user->createdWorkspaces()->create([
            'name' => data_get($request, 'workspace'),
            'description' => 'Default workspace',
        ]);

        $workspace->members()->attach($user->id);

        $token = SessionToken::generate('signup_verification_token', $user);

        Mail::to(data_get($request, 'email'))->send(new SignupVerificationEmail($user,$token));

        return response()->success([
            'user' => UserResource::make($user)
        ], 'Signup successfull!. Please check your email for verification link.');
    }

    public function verifySignup(Request $request)
    {
        $user = data_get($request, 'verified_user');
        $tokenRecord = data_get($request, 'token_record');

        // Activate the user
        $user->update([
            'is_active' => true
        ]);

        // Delete the verification token
        $tokenRecord->delete();

        return response()->success([
            'user' => UserResource::make($user)
        ], 'Account activated successfully! You can now login.');
    }

    public function login(Request $request)
    {
        $user = data_get($request, 'user');

        $token = SessionToken::generate('login_token', $user);

        // Store encrypted token in user model
        $user->update(['access_token' => hash('sha256', $token)]);

        return response()->success([
            'access_token' => $token,
            'user' => UserResource::make($user)
        ], 'Login successful!');
    }

    public function logout(Request $request)
    {
        $tokenRecord = data_get($request, 'token_record');
        $user = data_get($request, 'user');

        // Clear access_token from user model
        $user->update(['access_token' => null]);

        // Delete associated FCM tokens on logout
        \App\Models\FcmToken::where('user_id', $user->_id)->delete();

        $tokenRecord->delete();

        return response()->success(null, 'Logout successful!');
    }

    public function forgotPassword(Request $request)
    {
        $user = data_get($request, 'user');

        $token = ForgetToken::generate('forgot_password_token', $user);

        
        Mail::to(data_get($user, 'email'))->send(new \App\Mail\ResetPasswordEmail($user, $token));


        Mail::to($user->email)->send(new \App\Mail\ResetPasswordEmail($user, $token));


        return response()->success([
            'forgot_password_token' => $token
        ], 'Password reset link sent to your email.');
    }

    public function resetPassword(Request $request)
    {
        $tokenRecord = data_get($request, 'token_record');

        $user = User::find($tokenRecord->user_id);

        $user->update([
            'password' => data_get($request, 'password')
        ]);

        $tokenRecord->delete();

        return response()->success(null, 'Password reset successfully!');
    }
}
