<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class Token extends Eloquent
{
    protected $collection = 'tokens';

    protected $guarded = ['key'];

    public static function randomKey($size)
    {
        do {
            $key = openssl_random_pseudo_bytes($size, $strongEnough);
        } while (! $strongEnough);

        $key = str_replace('+', '', base64_encode($key));
        $key = str_replace('/', '', $key);

        return base64_encode($key);
    }

    public static function getInstance()
    {
        $token = new self();
        $token->key = self::randomKey(32);

        return $token;
    }

    public static function userFor($token)
    {
        $token = self::where('key', '=', $token)->first();
        if (empty($token)) {
            return;
        }

        return User::find($token->user_id);
    }

    public static function isUserToken($user_id, $token)
    {
        return self::where('user_id', '=', $user_id)
            ->where('key', '=', $token)
            ->exists();
    }
}
