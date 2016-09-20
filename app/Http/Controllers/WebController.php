<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Contracts\Auth\Factory as Auth;

class WebController extends BaseController
{
    use ValidatesRequests;

    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Make a new WebController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->middleware('auth:web', ['only' => ['home', 'getLogout']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return view('login');
    }

    /**
     * Handle submissions of the login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('username', 'password');
        if (! $this->auth->guard('web')->attempt($credentials, true)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'These credentials do not match our records.']);
        }

        return redirect()->intended('/');
    }

    /**
     * Log a user out from Northstar, preventing one-click
     * sign-ons to other DoSomething.org websites.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        $this->auth->guard('web')->logout();

        return redirect('login');
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        return view('register');
    }

    /**
     * Handle submissions of the registration form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'email' => 'required|email',
            'birthdate' => 'required|date',
            'mobile' => 'mobile',
            'password' => 'required|confirmed|min:6',
        ]);
    }
}
