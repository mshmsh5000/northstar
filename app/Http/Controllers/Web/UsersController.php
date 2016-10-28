<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Routing\Controller as BaseController;
use Northstar\Models\User;

class UsersController extends BaseController
{
    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Make a new UsersController, inject dependencies and
     * set middleware for this controller's methods.
     *
     * @param Auth      $auth
     * @param Registrar $registrar
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff', ['only' => ['show']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('users.show', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        $authUser = auth()->guard('web')->user()->toArray();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        dd('editing... more to come...');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        dd('updating... more to come...');
    }
}
