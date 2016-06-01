<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Http\Transformers\TokenTransformer;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Contracts\Auth\Guard as Auth;
use Northstar\Auth\Registrar;

class AuthController extends Controller
{
    /**
     * The authentication guard.
     * @var \Northstar\Auth\NorthstarTokenGuard
     */
    protected $auth;

    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * @var TokenTransformer
     */
    protected $transformer;

    /**
     * Validation rules for login routes.
     * @var array
     */
    protected $loginRules = [
        'email' => 'email|required_without:mobile',
        'mobile' => 'required_without:email',
        'password' => 'required',
    ];

    /**
     * AuthController constructor.
     * @param Auth $auth
     * @param Registrar $registrar
     */
    public function __construct(Auth $auth, Registrar $registrar)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;

        $this->transformer = new TokenTransformer();

        $this->middleware('scope:user');
        $this->middleware('auth', ['only' => 'invalidateToken']);
        $this->middleware('guest', ['except' => 'invalidateToken']);
    }

    /**
     * Authenticate a registered user based on the given credentials,
     * and return an authentication token.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function createToken(Request $request)
    {
        $request = $this->registrar->normalize($request);
        $this->validate($request, $this->loginRules);

        $credentials = $request->only('email', 'mobile', 'password');
        $token = $this->registrar->login($credentials);

        return $this->item($token, 201);
    }

    /**
     * Verify user credentials without making a session.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function verify(Request $request)
    {
        $request = $this->registrar->normalize($request);
        $this->validate($request, $this->loginRules);

        $credentials = $request->only('email', 'mobile', 'password');
        $user = $this->registrar->resolve($credentials);

        if (! $this->registrar->verify($user, $credentials)) {
            throw new UnauthorizedHttpException(null, 'Invalid credentials.');
        }

        return $this->item($user, 200, [], new UserTransformer());
    }

    /**
     * Logout the current user by invalidating their session token.
     * POST /logout
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function invalidateToken(Request $request)
    {
        $token = $this->auth->token();

        // Attempt to delete token.
        $deleted = $token->delete();
        if (! $deleted) {
            app('stathat')->ezCount('logout error');
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

    /**
     * Authenticate a registered user
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws UnauthorizedHttpException
     */
    public function register(Request $request)
    {
        $request = $this->registrar->normalize($request);

        // If a user exists but has not set a password yet, allow them to
        // "register" to set a new password on their account.
        $credentials = $request->only('email', 'mobile');
        $existing = $this->registrar->resolve($credentials);
        if ($existing && $existing->hasPassword()) {
            throw new HttpException(422, 'A user with that email or mobile has already been registered.');
        }

        $this->registrar->validate($request, $existing, ['password' => 'required']);

        $user = $this->registrar->register($request->except(User::$internal), $existing);

        // Should we try to make a Drupal account for this user?
        if ($request->has('create_drupal_user') && $request->has('password') && ! $user->drupal_id) {
            $user = $this->registrar->createDrupalUser($user, $request->input('password'));
            $user->save();
        }

        $token = $this->registrar->createToken($user);

        return $this->item($token);
    }
}
