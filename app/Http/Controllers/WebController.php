<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Factory as Auth;

class WebController extends Controller
{
    /**
     * Make a new HomeController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return redirect()->to('https://www.dosomething.org');
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
     * the login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        return $this->respond('Not yet implemented.', 501);
    }

    /**
     * Placeholder logout route.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogout()
    {
        return $this->respond('Not yet implemented.', 501);
    }
}
