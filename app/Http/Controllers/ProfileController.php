<?php

namespace Northstar\Http\Controllers;

use Auth;
use Illuminate\Contracts\Auth\Guard;
use Northstar\Http\Transformers\UserTransformer;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    public function __construct()
    {
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
        // Find the user.
        $user = Auth::user();

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
        $user = Auth::user();

        $this->validate($request, [
            'email' => 'email|max:60|unique:users,email,'.$user->id.',_id',
            'mobile' => 'unique:users,mobile,'.$user->id.',_id',
        ]);

        $user->fill($request->all());
        $user->save();

        return $this->item($user);
    }
}
