<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

class ForgetToken extends Model
{
    protected $collection = 'session_tokens';

    protected $fillable = [
        'token_type',
        'user_id',
        'token',
    ];

    /**
     * Generate new forget password token and delete old tokens for user
     */
    public static function generate($token_type, $user){
        // Delete old forget password tokens for this user
        if ($user) {
            self::where('user_id', $user->_id)
                ->where('token_type', $token_type)
                ->delete();
        }
            
        $plain_token = Str::random(32).now()->timestamp;
        $token = ForgetToken::create([
            'token_type'=>$token_type,
            'user_id'=>$user ? $user->_id : null,
            'token'=>hash('sha256', $plain_token),
        ]);

        return $plain_token;
    }

    /**
     * Find valid forget password token
     */
    public static function findValidToken($token, $token_type)
    {
        return self::where('token', hash('sha256', $token))
            ->where('token_type', $token_type)
            ->first();
    }
}
