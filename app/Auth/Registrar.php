<?php

namespace Northstar\Auth;

use Hash;
use Illuminate\Contracts\Auth\Guard;
use Northstar\Models\Token;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Registrar
{
    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Registrar constructor.
     * @param Guard $guard
     * @param Phoenix $phoenix
     */
    public function __construct(Guard $guard, Phoenix $phoenix)
    {
        $this->guard = $guard;
        $this->phoenix = $phoenix;
    }

    /**
     * Authenticate a user based on the given credentials,
     * and create a new session token.
     *
     * @param array $credentials
     * @return mixed
     */
    public function login($credentials)
    {
        $user = $this->resolve($credentials);

        if (! $this->verify($user, $credentials)) {
            throw new UnauthorizedHttpException(null, 'Invalid credentials.');
        }

        return $this->createToken($user);
    }

    /**
     * Resolve a user account from the given credentials.
     *
     * @param array $credentials
     * @return User|null
     */
    public function resolve($credentials)
    {
        if ($credentials['email']) {
            $email = strtolower($credentials['email']);

            return User::where('email', $email)->first();
        }

        if ($credentials['mobile']) {
            return User::where('mobile', $credentials['mobile'])->first();
        }
    }

    /**
     * Verify the given user and credentials. If the user has a Drupal
     * password & it matches, re-hash and save to the user document.
     *
     * @param User $user
     * @param array $credentials
     * @return bool
     */
    public function verify($user, $credentials)
    {
        if (! $user) {
            return false;
        }

        if (Hash::check($credentials['password'], $user->password)) {
            return true;
        }

        if (! $user->password && DrupalPasswordHash::check($credentials['password'], $user->drupal_password)) {
            // If this user has a Drupal-hashed password, rehash it, remove the
            // Drupal password field from the user document, and save the user.
            $user->password = $credentials['password'];
            $user->unset('drupal_password');
            $user->save();

            return true;
        }

        // Well, looks like we couldn't authenticate...
        return false;
    }

    /**
     * Create a new authentication token & set the active user.
     *
     * @param User $user
     * @return Token
     */
    public function createToken($user)
    {
        $token = $user->login();

        $this->guard->setUser($user);

        return $token;
    }

    /**
     * Create a new user.
     *
     * @return User|null
     */
    public function register($input, $user = null)
    {
        // If there is no user provided, create a new one.
        if (! $user) {
            $user = new User;
        }

        $user->fill($input);
        $user->save();

        return $user;
    }

    /**
     * @param $user
     * @param $password
     * @return mixed
     */
    public function createDrupalUser($user, $password)
    {
        // @TODO: we can't create a Drupal user without an email. Do we just create an @mobile one like we had done previously?
        try {
            $drupal_id = $this->phoenix->register($user, $password);
            $user->drupal_id = $drupal_id;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // If user already exists (403 Forbidden), try to find the user to get the UID.
            if ($e->getCode() == 403) {
                try {
                    $drupal_id = $this->phoenix->getUidByEmail($user->email);
                    $user->drupal_id = $drupal_id;
                } catch (\Exception $e) {
                    // @TODO: still ok to just continue and allow the user to be saved?
                }
            }
        }

        return $user;
    }
}
