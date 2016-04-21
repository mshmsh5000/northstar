<?php

namespace Northstar\Http\Controllers;

use Northstar\Http\Transformers\UserTransformer;
use Northstar\Auth\Scope;
use Northstar\Services\AWS;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Gate;

class AvatarController extends Controller
{
    /**
     * Amazon Web Services API wrapper.
     * @var AWS
     */
    protected $aws;

    /**
     * @var UserTransformer
     */
    protected $transformer;

    public function __construct(AWS $aws)
    {
        $this->aws = $aws;

        $this->transformer = new UserTransformer();

        $this->middleware('scope:user');
        $this->middleware('auth');
    }

    /**
     * Save a new avatar to a user's profile.
     * POST /users/:id/avatar
     *
     * @param Request $request
     * @param $id - User ID
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $this->validate($request, [
            'photo' => 'required',
        ]);

        $user = User::where('_id', $id)->first();

        if (! $user) {
            throw new NotFoundHttpException('The resource does not exist.');
        }

        // Only the currently authorized user to edit their own profile
        // or, if using an `admin` scoped API key, any profile.
        $allowed = Scope::allows('admin') || Gate::allows('edit-profile', $user);
        if (! $allowed) {
            throw new UnauthorizedHttpException('auth/token', 'You are not authorized to edit that user\'s avatar.');
        }

        // If a file is attached via multipart/form-data, use that. Otherwise, look
        // for a Base-64 encoded Data URI in the request body.
        $file = $request->file('photo') ? $request->file('photo') : $request->photo;
        $filename = $this->aws->storeImage('avatars', $id, $file);

        // Save filename to User model
        $user->photo = $filename;
        $user->save();

        // Respond to user with success and photo URL
        return $this->item($user);
    }
}
