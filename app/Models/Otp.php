<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;

class Otp extends Eloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'otps';

    protected $fillable = [
        'email',
        'otp',
        'type',
        'expires_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate a 6-digit OTP
     */
    public static function generateOtp()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create OTP for email
     */
    public static function createForEmail($email, $type, $expiresInMinutes = 5)
    {
        // Invalidate any existing OTPs for this email and type
        self::where('email', $email)
            ->where('type', $type)
            ->where('is_used', false)
            ->update(['is_used' => true, 'used_at' => now()]);

        return self::create([
            'email' => $email,
            'otp' => self::generateOtp(),
            'type' => $type,
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'is_used' => false,
        ]);
    }

    /**
     * Find valid OTP
     */
    public static function findValidOtp($email, $otp, $type)
    {
        return self::where('email', $email)
            ->where('otp', $otp)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed()
    {
        $this->is_used = true;
        $this->used_at = now();
        return $this->save();
    }

    /**
     * Clean up expired OTPs
     */
    public static function cleanupExpired()
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Scope to get only active OTPs
     */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now());
    }
}
