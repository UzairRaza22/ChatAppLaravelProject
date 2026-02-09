<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmailVerificationResource;
use App\Http\Resources\OtpResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\TokenResource;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Models\User;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\CompleteRegistrationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\CreateTokenRequest;
use App\Http\Requests\Auth\RevokeTokenRequest;
use App\Http\Requests\Auth\DeleteTokenRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        // Check if user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This email is already registered'
            ], 422);
        }

        // Create user without OTP verification (will be verified later)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => false, // Will be active after OTP verification
        ]);

        // Automatically send OTP for registration
        $otpResult = $this->otpService->sendOtp($request->email, 'registration');
        
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. OTP has been sent to your email.',
            'data' => [
                'user' => new UserResource($user),
                'otp_sent' => true,
                'expires_at' => $otpResult['expires_at'] ?? null
            ]
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not registered'
            ], 401);
        }
        
        // Check password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // If OTP is provided, verify it
        if (!empty($request->otp)) {
            $otpResult = $this->otpService->verifyOtp($request->email, $request->otp, 'registration');
            
            if (!$otpResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP'
                ], 401);
            }
            
            // Activate user if not already active
            if (!$user->is_active) {
                $user->is_active = true;
                $user->save();
            }
        } else {
            // Check if OTP is already verified
            if (!$user->isOtpVerified()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP field is required for login'
                ], 422);
            }
        }
        
        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active'
            ], 401);
        }
        
        // Create custom API token
        $token = ApiToken::createToken($user, 'Login Token', now()->addDays(30));
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->token,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at
            ]
        ], 200);
    }

    /**
     * Login user with OTP verification
     */
    public function loginWithOtp(Request $request)
    {
        // Validate required fields
        if (empty($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email field is required'
            ], 422);
        }

        if (empty($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password field is required'
            ], 422);
        }

        if (empty($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP field is required'
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Check password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Verify OTP
        $otpResult = $this->otpService->verifyOtp($request->email, $request->otp, 'registration');
        
        if (!$otpResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }
        
        // Activate user if not already active
        if (!$user->is_active) {
            $user->is_active = true;
            $user->save();
        }
        
        // Create custom API token
        $token = ApiToken::createToken($user, 'Login Token', now()->addDays(30));
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful with OTP verification',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->token,
                'token_type' => 'Bearer',
                'expires_at' => $token->expires_at
            ]
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token is required for logout'
            ], 422);
        }
        
        ApiToken::revokeToken($token);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Delete OTP verification
     */
    public function deleteOtpVerification(Request $request)
    {
        // Validate required fields
        if (empty($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email field is required'
            ], 422);
        }

        if (empty($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password field is required'
            ], 422);
        }

        if (empty($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP field is required'
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Check password
        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        
        // Verify OTP
        $otpResult = $this->otpService->verifyOtp($request->email, $request->otp, 'registration');
        
        if (!$otpResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }
        
        // Delete user account
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ], 200);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * Change password
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return new SuccessResource(['message' => 'Password changed successfully']);
    }

    /**
     * Refresh token
     */
    public function refresh(RefreshTokenRequest $request)
    {
        $token = $request->bearerToken();
        $apiToken = ApiToken::where('token', $token)->first();
        $apiToken->markAsRevoked();
        $newToken = ApiToken::createToken($apiToken->user, 'token_refresh');

        return new TokenResource(['token' => $newToken]);
    }

    /**
     * List all users (public endpoint)
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->paginate(20);

        return UserResource::collection($users);
    }

    /**
     * Get all tokens for the authenticated user
     */
    public function tokens(Request $request)
    {
        $tokens = $request->user()->apiTokens()
            ->orderBy('created_at', 'desc')
            ->get();

        return new TokenResource(['token' => $token]);
    }

    /**
     * Create a new API token
     */
    public function createToken(CreateTokenRequest $request)
    {
        $token = ApiToken::createToken(
            $request->user(),
            $request->name,
            $request->expires_at ?? null
        );

        return new TokenResource(['token' => $token]);
    }

    /**
     * Get current token information
     */
    public function currentToken(Request $request)
    {
        $token = $request->bearerToken();
        $apiToken = ApiToken::where('token', $token)->first();

        return new TokenResource(['token' => $apiToken]);
    }

    /**
     * Refresh current token
     */
    public function refreshToken(RefreshTokenRequest $request)
    {
        $apiToken = ApiToken::where('token', $request->token)->first();
        $apiToken->markAsRevoked();
        $newToken = ApiToken::createToken($apiToken->user, 'token_refresh');

        return new TokenResource(['token' => $newToken, 'old_token_revoked' => true]);
    }

    /**
     * Revoke a specific token
     */
    public function revokeToken(RevokeTokenRequest $request, $id)
    {
        $token = $request->user()->apiTokens()->findOrFail($id);
        $token->markAsRevoked();

        return new SuccessResource(['message' => 'Token revoked successfully']);
    }

    /**
     * Delete a specific token
     */
    public function deleteToken(DeleteTokenRequest $request, $id)
    {
        $token = $request->user()->apiTokens()->findOrFail($id);
        $token->delete();

        return new SuccessResource(['message' => 'Token deleted successfully']);
    }

    /**
     * Send OTP for registration or forgot password
     */
    public function sendOtp(SendOtpRequest $request)
    {
        // Check if email is provided
        if (empty($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email field is required'
            ], 422);
        }

        $result = $this->otpService->sendOtp(
            $request->email,
            $request->type
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => [
                'expires_at' => $result['expires_at']
            ]
        ], 200);
    }

    /**
     * Verify OTP for registration
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        // Check if all required fields are provided
        if (empty($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email field is required'
            ], 422);
        }

        if (empty($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP field is required'
            ], 422);
        }

        $result = $this->otpService->verifyOtp(
            $request->email,
            $request->otp,
            $request->type
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if ($request->type === 'registration' && !$user) {
            $user = User::create([
                'email' => $request->email,
                'name' => 'Pending User',
                'password' => bcrypt('temp_password_' . time()),
                'is_active' => false,
            ]);
            
            $token = ApiToken::createToken($user, 'otp_verification');
        }

        // Activate user after successful OTP verification
        if ($user) {
            $user->is_active = true;
            $user->otp_verified_at = now();
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. You can now login.',
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * Complete registration after OTP verification
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->update([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'otp_verified_at' => now(),
            'is_active' => true
        ]);

        $token = ApiToken::createToken($user, 'registration_complete');

        return new UserResource($user);
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Check if all required fields are provided
        if (empty($request->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Email field is required'
            ], 422);
        }

        if (empty($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP field is required'
            ], 422);
        }

        if (empty($request->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password field is required'
            ], 422);
        }

        if (empty($request->password_confirmation)) {
            return response()->json([
                'success' => false,
                'message' => 'Password confirmation field is required'
            ], 422);
        }

        if ($request->password !== $request->password_confirmation) {
            return response()->json([
                'success' => false,
                'message' => 'Password confirmation does not match'
            ], 422);
        }

        $result = $this->otpService->resetPassword(
            $request->email,
            $request->otp,
            $request->password
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login with your new password.'
        ], 200);
    }

    /**
     * Mark the authenticated user's email address as verified
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);
        $user->markEmailAsVerified();

        return new SuccessResource(['message' => 'Email verified successfully']);
    }

    /**
     * Resend the email verification notification
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return new SuccessResource(['message' => 'Verification email sent successfully']);
    }

    /**
     * Check email verification status
     */
    public function checkEmailVerification(Request $request)
    {
        return new EmailVerificationResource([
            'verified' => $request->user()->hasVerifiedEmail(),
            'email' => $request->user()->email
        ]);
    }
}
