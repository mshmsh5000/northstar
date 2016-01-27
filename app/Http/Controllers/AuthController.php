<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Northstar\Services\Registrar;
use Auth;

class AuthController extends Controller
{
    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('key:user');
        $this->middleware('auth', ['only' => 'logout']);
    }

    /**
     * Authenticate a registered user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function login(Request $request)
    {
        $input = $request->only('email', 'mobile', 'password');

        $this->validate($request, [
            'email' => 'email',
            'password' => 'required',
        ]);

        $user = $this->registrar->login($input);

        return $this->respond($user);
    }

    /**
     * Logout the current user by invalidating their session token.
     * POST /logout
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function logout(Request $request)
    {
        $token = Auth::token();

        if (! $token) {
            throw new NotFoundHttpException('No active session found.');
        }

        // Attempt to delete token.
        $deleted = $token->delete();
        if (! $deleted) {
            throw new HttpException(400, 'User could not log out. Please try again.');
        }

        // Remove Parse installation ID. Disables push notifications.
        $user = $token->user;
        if ($user && $request->has('parse_installation_ids')) {
            $removeIds = $request->input('parse_installation_ids');
            $user->pull('parse_installation_ids', $removeIds);
            $user->save();
        }

        return $this->respond('User logged out successfully.');
    }
}
