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
}
