<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class Token extends Eloquent
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];


    /**
     * Generate a random key of given length.
     *
     * @param $size
     * @return string
     */
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

    /**
     * Get the user associated with a given token key.
     * @param int $token - Token key
     */
    public static function userFor($token)
    {
        $token = self::where('key', '=', $token)->first();
        if (empty($token)) {
            return;
        }

        return User::find($token->user_id);
    }

    /**
     * Check if this given token is associated with the given user.
     *
     * @param int   $user_id
     * @param Token $token
     * @return mixed
     */
    public static function isUserToken($user_id, $token)
    {
        return self::where('user_id', '=', $user_id)
            ->where('key', '=', $token)
            ->exists();
    }
}
