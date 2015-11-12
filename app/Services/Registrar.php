<?php namespace Northstar\Services;

use Northstar\Models\User;
use Northstar\Models\Token;
use Hash;
use Northstar\Services\DrupalPasswordChecker;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Registrar
{
    public function __construct(DrupalPasswordChecker $drupal_password_checker)
    {
        $this->drupal_password_checker = $drupal_password_checker;
    }

    public function login($input)
    {
        $login_type = 'username';
        if ($input['email']) {
            $email = strtolower($input['email']);
            $user = User::where('email', '=', $email)->first();
            $login_type = 'email';
        } elseif ($input['mobile']) {
            $user = User::where('mobile', '=', $input['mobile'])->first();
            $login_type = 'mobile';
        }

        if (($user instanceof User) && Hash::check($input['password'], $user->password)) {
            $token = $user->login();
            $token->user = $user->toArray();

            // Return the session token with the user.
            $user->session_token = $token->key;
            return $user;
        } else if (($user instanceof User) && !($user->password)) {

            // check to see if $input['password'] equals user's drupal password
            if ($this->drupal_password_checker->user_check_password($input['password'], $user->drupal_password)) {

                // if they're the same, make $input['password'] into a hash and save it to the user.
                $user->password = $input['password'];
                $user->drupal_password = null; // @TODO
                $user->save();

                $token = $user->login();
                $token->user = $user->toArray();

                // Return the session token with the user.
                $user->session_token = $token->key;
                return $user;
            }
        } else {
            throw new UnauthorizedHttpException(null, 'Invalid ' . $login_type . ' or password.');
        }
    }
}
