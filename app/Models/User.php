<?php

namespace App\Models;

use App\Notifications\EmailVerificationNotification;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model implements AuthenticatableContract, MustVerifyEmail
{
    use Authenticatable, HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'otp_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'otp_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function workspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, null, 'user_ids', 'team_ids');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if user is OTP verified
     */
    public function isOtpVerified()
    {
        return !is_null($this->otp_verified_at);
    }

    /**
     * Mark user as OTP verified
     */
    public function markOtpVerified()
    {
        $this->otp_verified_at = now();
        return $this->save();
    }

    /**
     * Check if user can login (OTP verified)
     */
    public function canLogin()
    {
        return $this->is_active && $this->isOtpVerified();
    }
}
