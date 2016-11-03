<?php

namespace Northstar\Http\Controllers;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PasswordController extends BaseController
{
    use ValidatesRequests, ResetsPasswords;

    /**
     * The authentication guard that should be used.
     *
     * @var string
     */
    protected $guard = 'web';

    /**
     * Reset the given user's password.
     *
     * @param  \Northstar\Models\User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $attributes = [
            'password' => $password,
            'remember_token' => str_random(60),
        ];

        $user->forceFill($attributes)->save();

        // And create a Northstar session for the user.
        auth()->guard($this->getGuard())->login($user);
    }

    /**
     * Get the path to redirect to after resetting a password.
     *
     * @return string
     */
    public function redirectPath()
    {
        return config('services.drupal.url').'/user/authorize';
    }
}
