<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;

class ApiToken extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'api_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'name',
        'abilities',
        'expires_at',
        'last_used_at',
        'ip_address',
        'user_agent',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    /**
     * Generate a secure random token
     */
    public static function generateToken()
    {
        return 'whistle_' . Str::random(64) . '_' . time();
    }

    /**
     * Create a new API token for a user
     */
    public static function createToken(User $user, string $name = 'API Token', $expiresAt = null)
    {
        return self::create([
            'user_id' => $user->_id,
            'token' => self::generateToken(),
            'name' => $name,
            'abilities' => ['*'], // Full access
            'expires_at' => $expiresAt ?: now()->addDays(30),
            'is_active' => true,
        ]);
    }

    /**
     * Find a token by its value
     */
    public static function findByToken($token)
    {
        return self::where('token', $token)->first();
    }

    /**
     * Find valid token by string
     */
    public static function findValidToken($token)
    {
        return self::where('token', $token)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Check if token is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is valid (active and not expired)
     */
    public function isValid()
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Revoke the token
     */
    public function revoke()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Mark token as used
     */
    public function markAsRevoked()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Update token usage information
     */
    public function updateUsage($request = null)
    {
        $this->last_used_at = now();
        
        if ($request) {
            $this->ip_address = $request->ip();
            $this->user_agent = $request->userAgent();
        }
        
        return $this->save();
    }

    /**
     * Check if token has specific ability
     */
    public function can($ability)
    {
        if (in_array('*', $this->abilities)) {
            return true;
        }
        
        return in_array($ability, $this->abilities);
    }

    /**
     * Get the user that owns the token
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope to get only active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Revoke token by token string
     */
    public static function revokeToken($token)
    {
        $apiToken = self::findByToken($token);
        if ($apiToken) {
            return $apiToken->revoke();
        }
        return false;
    }

    /**
     * Scope to get only expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}
