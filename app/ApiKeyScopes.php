<?php

namespace Northstar;

class ApiKeyScopes {

    /**
     * API Key scopes.
     * @var array
     */
    protected static $scopes = [
        'admin' => 'Allows "administrative" actions that should not be user-accessible, like deleting user records.',
        'user' => 'Allows actions to be made on a user\'s behalf.',
    ];

    /**
     * Validate if all the given scopes are valid.
     *
     * @param $scopes
     * @return boolean
     */
    public static function validate($scopes)
    {
        if(! is_array($scopes)) return false;

        return !array_diff($scopes, array_keys(static::all()));
    }

    /**
     * Return a list of all scopes & their descriptions.
     * @return array
     */
    public static function all()
    {
        return static::$scopes;
    }

}
