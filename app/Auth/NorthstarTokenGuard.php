<?php

namespace Northstar\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Mockery\CountValidator\Exception;
use Northstar\Models\Token;
use Northstar\Models\User;

class NorthstarTokenGuard extends TokenGuard implements Guard
{
    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);

        $this->inputKey = 'token';
        $this->storageKey = 'key';
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $token = $this->getTokenForRequest();

        if (! empty($token)) {
            $user = $this->getUserForToken($token);
        }

        return $this->user = $user;
    }

    /**
     * Get the associated user for a given token string.
     *
     * @param string $tokenKey
     * @return \Northstar\Models\User|null
     */
    public function getUserForToken($tokenKey)
    {
        // If the provided token is 32 characters long, it's a legacy
        // database token. Otherwise, it must be a JWT access token.
        if (strlen($tokenKey) === 32) {
            return $this->getUserFromLegacyToken($tokenKey);
        }

        return $this->getUserFromJWTAccessToken();
    }

    /**
     * Fetch a legacy authentication token from the database to
     * get it's corresponding user.
     *
     * @param $tokenKey
     * @return User|null
     */
    public function getUserFromLegacyToken($tokenKey)
    {
        $token = Token::where($this->storageKey, $tokenKey)->first();

        if (empty($token)) {
            return null;
        }

        return User::find($token->user_id);
    }

    /**
     * Retrieve the user from a parsed JWT access token that
     * was provided with the current request.
     *
     * @return User|null
     */
    public function getUserFromJWTAccessToken()
    {
        $id = request()->attributes->get('oauth_user_id');

        if (empty($id)) {
            return null;
        }

        return User::find($id);
    }

    /**
     * Get the token key for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        // Support deprecated "Session" header authentication.
        $tokenKey = $this->request->header('Session');

        if (! empty($tokenKey)) {
            return $tokenKey;
        }

        return parent::getTokenForRequest();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if ($this->getUserForToken($credentials[$this->inputKey])) {
            return true;
        }

        return false;
    }

    /**
     * Get the legacy Token instance for the current request.
     *
     * @return Token|null
     */
    public function token()
    {
        $tokenKey = $this->getTokenForRequest();

        // If the provided token is 32 characters long, it's a legacy
        // database token. Otherwise, it must be a JWT access token,
        // which does not have a corresponding database record.
        if (strlen($tokenKey) === 32) {
            return Token::where($this->storageKey, $tokenKey)->first();
        }

        return null;
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @throws Exception
     */
    public function once()
    {
        throw new Exception('Token-based authentication is stateless. Use Auth::check instead.');
    }
}
