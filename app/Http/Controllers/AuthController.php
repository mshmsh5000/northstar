<?php

namespace Northstar\Http\Controllers;

use Northstar\Models\Token;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Northstar\Services\Registrar;

class AuthController extends Controller
{
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;
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
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function logout(Request $request)
    {
        if (! $request->header('Session')) {
            throw new HttpException(422, 'No token given.');
        }

        $input_token = $request->header('Session');
        $token = Token::where('key', '=', $input_token)->first();
        $user = Token::userFor($input_token);

        if (empty($token)) {
            throw new NotFoundHttpException('No active session found.');
        } elseif ($token->user_id !== $user->_id) {
            throw new HttpException(403, 'You do not own this token.');
        } elseif ($token->delete()) {
            // Remove Parse installation ID. Disables push notifications.
            if ($request->has('parse_installation_ids')) {
                $removeId = $request->parse_installation_ids;
                $user->pull('parse_installation_ids', $removeId);
                $user->save();
            }

            return $this->respond('User logged out successfully.');
        } else {
            throw new HttpException(400, 'User could not log out. Please try again.');
        }
    }
}
