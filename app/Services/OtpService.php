<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Send OTP (unified method for both registration and forgot password)
     */
    public function sendOtp($email, $type)
    {
        try {
            // Clean up expired OTPs
            Otp::cleanupExpired();

            if ($type === 'registration') {
                return $this->sendRegistrationOtp($email);
            } elseif ($type === 'forgot_password') {
                return $this->sendForgotPasswordOtp($email);
            }

            throw new \InvalidArgumentException('Invalid OTP type. Must be registration or forgot_password.');

        } catch (\Exception $e) {
            Log::error('Failed to send OTP', [
                'email' => $email,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ];
        }
    }

    /**
     * Generate and send OTP for registration
     */
    public function sendRegistrationOtp($email, $name = null)
    {
        try {
            // Clean up expired OTPs
            Otp::cleanupExpired();

            // Create OTP
            $otp = Otp::createForEmail($email, 'registration', 5);

            // Send email
            Mail::send('emails.otp-registration', [
                'name' => $name,
                'otp' => $otp->otp,
                'email' => $email
            ], function ($message) use ($email, $name) {
                $message->to($email)
                    ->subject('Verify Your Email - Whistle IT Registration');
            });

            Log::info('Registration OTP sent', [
                'email' => $email,
                'otp' => $otp->otp,
                'expires_at' => $otp->expires_at
            ]);

            return [
                'success' => true,
                'message' => 'OTP sent successfully. Please check your email.',
                'expires_at' => $otp->expires_at
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send registration OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ];
        }
    }

    /**
     * Generate and send OTP for forgot password
     */
    public function sendForgotPasswordOtp($email, $name = null)
    {
        try {
            // Check if user exists
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'No account found with this email address.'
                ];
            }

            // Clean up expired OTPs
            Otp::cleanupExpired();

            // Create OTP
            $otp = Otp::createForEmail($email, 'forgot_password', 5);

            // Send email
            Mail::send('emails.otp-forgot-password', [
                'name' => $name ?? $user->name,
                'otp' => $otp->otp,
                'email' => $email
            ], function ($message) use ($email, $name) {
                $message->to($email)
                    ->subject('Reset Your Password - Whistle IT');
            });

            Log::info('Forgot password OTP sent', [
                'email' => $email,
                'otp' => $otp->otp,
                'expires_at' => $otp->expires_at
            ]);

            return [
                'success' => true,
                'message' => 'Password reset OTP sent successfully. Please check your email.',
                'expires_at' => $otp->expires_at
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send forgot password OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ];
        }
    }

    /**
     * Verify OTP (unified method for both registration and forgot password)
     */
    public function verifyOtp($email, $otp, $type)
    {
        try {
            if ($type === 'registration') {
                return $this->verifyRegistrationOtp($email, $otp);
            } elseif ($type === 'forgot_password') {
                // For forgot password, we don't verify here - we verify during reset
                $otpRecord = Otp::findValidOtp($email, $otp, 'forgot_password');
                
                if (!$otpRecord) {
                    return [
                        'success' => false,
                        'message' => 'Invalid or expired OTP. Please request a new one.'
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'OTP verified. You can now reset your password.',
                    'otp_verified' => true
                ];
            }

            throw new \InvalidArgumentException('Invalid OTP type. Must be registration or forgot_password.');

        } catch (\Exception $e) {
            Log::error('Failed to verify OTP', [
                'email' => $email,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ];
        }
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword($email, $otp, $newPassword)
    {
        return $this->verifyAndResetPassword($email, $otp, $newPassword);
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp($email, $otp)
    {
        try {
            $otpRecord = Otp::findValidOtp($email, $otp, 'registration');

            if (!$otpRecord) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.'
                ];
            }

            // Mark OTP as used
            $otpRecord->markAsUsed();

            // Find or create user (user might have been created during registration)
            $user = User::where('email', $email)->first();
            if ($user && !$user->isOtpVerified()) {
                $user->markOtpVerified();
            }

            Log::info('Registration OTP verified', [
                'email' => $email,
                'verified_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Email verified successfully. You can now login.',
                'user' => $user
            ];

        } catch (\Exception $e) {
            Log::error('Failed to verify registration OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ];
        }
    }

    /**
     * Verify OTP and reset password
     */
    public function verifyAndResetPassword($email, $otp, $newPassword)
    {
        try {
            $otpRecord = Otp::findValidOtp($email, $otp, 'forgot_password');

            if (!$otpRecord) {
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.'
                ];
            }

            // Find user
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found.'
                ];
            }

            // Mark OTP as used
            $otpRecord->markAsUsed();

            // Update password
            $user->password = bcrypt($newPassword);
            $user->save();

            Log::info('Password reset successfully', [
                'email' => $email,
                'reset_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Password reset failed. Please try again.'
            ];
        }
    }

    /**
     * Validate OTP format
     */
    public function validateOtpFormat($otp)
    {
        return preg_match('/^\d{6}$/', $otp);
    }

    /**
     * Validate password
     */
    public function validatePassword($password)
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }
}
