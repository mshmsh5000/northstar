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
        $token = Token::where($this->storageKey, $tokenKey)->first();

        if (empty($token)) {
            return;
        }

        return User::find($token->user_id);
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
     * Get the Token instance for the current request.
     *
     * @return Token
     */
    public function token()
    {
        $tokenKey = $this->getTokenForRequest();

        return Token::where($this->storageKey, $tokenKey)->first();
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
