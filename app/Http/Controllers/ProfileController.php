<?php

namespace Northstar\Http\Controllers;

use Auth;
use Illuminate\Contracts\Auth\Guard;
use Northstar\Auth\Registrar;
use Northstar\Http\Transformers\UserTransformer;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var Guard
     */
    protected $guard;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    public function __construct(Guard $guard, Registrar $registrar)
    {
        $this->guard = $guard;
        $this->registrar = $registrar;

        $this->transformer = new UserTransformer();

        $this->middleware('key:user');
        $this->middleware('auth');
    }

    /**
     * Display the current user's profile.
     * GET /profile
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        /** @var \Northstar\Models\User $user */
        $user = $this->guard->user();

        return $this->item($user);
    }

    /**
     * Update the currently authenticated user's profile.
     * PUT /profile
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /** @var \Northstar\Models\User $user */
        $user = $this->guard->user();

        // Normalize & validate the given request.
        $request = $this->registrar->normalize($request);
        $this->registrar->validate($request, $user);

        $user->fill($request->all());
        $user->save();

        return $this->item($user);
    }
}
