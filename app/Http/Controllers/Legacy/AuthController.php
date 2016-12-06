<?php

namespace Northstar\Http\Controllers\Legacy;

use Illuminate\Http\Request;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Transformers\TokenTransformer;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Models\Token;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Contracts\Auth\Factory as Auth;
use Northstar\Auth\Registrar;

class AuthController extends Controller
{
    /**
     * The authentication factory.
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The registrar.
     * @var Registrar
     */
    protected $registrar;

    /**
     * The Phoenix API client.
     * @var Phoenix
     */
    protected $phoenix;

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
     * @param Phoenix $phoenix
     */
    public function __construct(Auth $auth, Registrar $registrar, Phoenix $phoenix)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->phoenix = $phoenix;

        $this->transformer = new TokenTransformer();

        $this->middleware('scope:user');
        $this->middleware('auth', ['only' => ['invalidateToken', 'phoenix']]);
        $this->middleware('guest', ['except' => ['invalidateToken', 'phoenix']]);
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
        $request = normalize('credentials', $request);
        $this->validate($request, $this->loginRules);

        $credentials = $request->only('email', 'mobile', 'password');
        $user = $this->registrar->resolve($credentials);

        if (! $this->registrar->validateCredentials($user, $credentials)) {
            throw new UnauthorizedHttpException(null, 'Invalid credentials.');
        }

        // Create a legacy token & set the user for this request.
        $token = Token::create(['user_id' => $user->id]);
        $this->auth->guard('api')->setUser($user);

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
        $request = normalize('credentials', $request);
        $this->validate($request, $this->loginRules);

        $credentials = $request->only('email', 'mobile', 'password');
        $user = $this->registrar->resolve($credentials);

        if (! $this->registrar->validateCredentials($user, $credentials)) {
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
        $token = $this->auth->guard('api')->token();

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
        $request = normalize('credentials', $request);

        // If a user exists but has not set a password yet, allow them to
        // "register" to set a new password on their account.
        $credentials = $request->only('email', 'mobile');
        $existing = $this->registrar->resolve($credentials);
        if ($existing && $existing->hasPassword()) {
            throw new HttpException(422, 'A user with that email or mobile has already been registered.');
        }

        $this->registrar->validate($request, $existing, ['password' => 'required']);

        $user = $this->registrar->register($request->except(User::$internal), $existing);

        // Create a legacy token & set the user for this request.
        $token = Token::create(['user_id' => $user->id]);
        $this->auth->setUser($user);

        return $this->item($token);
    }

    /**
     * Create a Phoenix magic login link for the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function phoenix()
    {
        /** @var $user User */
        $user = $this->auth->user();

        // If the user doesn't have a Phoenix account, we certainly can't log them in.
        if (! $user->drupal_id) {
            throw new AccessDeniedHttpException('This user does not have a Phoenix account.');
        }

        return $this->phoenix->createMagicLogin($user->drupal_id);
    }
}
