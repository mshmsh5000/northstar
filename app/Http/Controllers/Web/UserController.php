<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Northstar\Auth\Registrar;
use Northstar\Models\User;

class UserController extends BaseController
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new UserController, inject dependencies and
     * set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

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
        $user = User::findOrFail($id);

        return view('users.edit', ['user' => $user]);
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
        $user = User::findOrFail($id);

        $this->registrar->validate($request, $user, [
            'first_name' => 'required',
            'last_name' => 'alpha',
            'birthdate' => 'required|date',
            'password' => 'min:6|confirmed',
        ]);

        $user->fill($request->all())->save();

        return redirect()->route('users.show', $user->id);
    }
}
