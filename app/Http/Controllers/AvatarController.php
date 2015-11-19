<?php

namespace Northstar\Http\Controllers;

use Northstar\Services\AWS;
use Northstar\Models\User;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function __construct(AWS $aws)
    {
        $this->aws = $aws;
    }

    /**
     * Store a new avatar for a user.
     * POST /users/:id/avatar
     *
     * @param Request $request
     * @param $id - User ID
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        if ($request->file('photo')) {
            $file = $request->file('photo');
        } else {
            $file = $request->photo;
        }

        $this->validate($request, [
            'photo' => 'required',
        ]);

        $filename = $this->aws->storeImage('avatars', $id, $file);

        // Save filename to User model
        $user = User::where('_id', $id)->first();
        $user->photo = $filename;
        $user->save();

        // Respond to user with success and photo URL
        return $this->respond($user);
    }
}
