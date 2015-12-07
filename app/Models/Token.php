<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

class Token extends Model
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
     * Create a new Token.
     *
     * @param  array  $attributes
     * @return Token
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random token key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        if(empty($this->key)) {
            $this->key = self::randomKey(32);
        }
    }

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

    /**
     * Create a new token. DEPRECATED: Use standard class
     * constructor instead.
     *
     * @return Token
     * @deprecated
     */
    public static function getInstance()
    {
        $token = new self();

        return $token;
    }

    /**
     * Get the user associated with a given token key.
     * @param int $token - Token key
     * @return User|null
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
