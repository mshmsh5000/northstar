<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use League\OAuth2\Server\AuthorizationServer;
use Northstar\Auth\Entities\UserEntity;
use Northstar\Auth\Registrar;
use Northstar\Exceptions\NorthstarValidationException;
use Northstar\Models\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends BaseController
{
    use ValidatesRequests;

    /**
     * The OAuth authorization server.
     *
     * @var AuthorizationServer
     */
    protected $oauth;

    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new WebController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Auth $auth
     * @param Registrar $registrar
     * @param AuthorizationServer $oauth
     */
    public function __construct(Auth $auth, Registrar $registrar, AuthorizationServer $oauth)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->oauth = $oauth;

        $this->middleware('guest:web', ['only' => ['getLogin', 'postLogin', 'getRegister', 'postRegister']]);
    }

    /**
     * Authorize an application via OAuth 2.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface|\Illuminate\Http\RedirectResponse
     */
    public function authorize(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Validate the HTTP request and return an AuthorizationRequest.
        $authRequest = $this->oauth->validateAuthorizationRequest($request);
        $client = $authRequest->getClient();

        // Store the Client ID so we can set user source on registrations.
        session(['authorize_client_id' => request()->query('client_id')]);

        if (! $this->auth->guard('web')->check()) {
            $destination = request()->query('destination', $client->getName());
            session(['destination' => $destination]);

            return redirect()->guest('login');
        }

        $user = UserEntity::fromModel($this->auth->guard('web')->user());
        $authRequest->setUser($user);

        // Clients are all our own at the moment, so they will always be approved.
        // @TODO: Add an explicit "DoSomething.org app" boolean to the Client model.
        $authRequest->setAuthorizationApproved(true);

        // Return the HTTP redirect response.
        return $this->oauth->completeAuthorizationRequest($authRequest, $response);
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle submissions of the login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('username', 'password');
        if (! $this->auth->guard('web')->attempt($credentials, true)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'These credentials do not match our records.']);
        }

        // If we had stored a destination name, reset it.
        session()->pull('destination');

        return redirect()->intended('/');
    }

    /**
     * Log a user out from Northstar, preventing one-click
     * sign-ons to other DoSomething.org websites.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLogout(Request $request)
    {
        // A custom post-logout redirect can be specified with `/logout?redirect=`
        $redirect = $request->query('redirect', 'login');

        $this->auth->guard('web')->logout();

        return redirect($redirect);
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle submissions of the registration form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws NorthstarValidationException
     */
    public function postRegister(Request $request)
    {
        // If a user exists but has not set a password yet, allow them to
        // "register" to set a new password on their account.
        $existing = $this->registrar->resolve($request);
        if ($existing && $existing->hasPassword()) {
            throw new NorthstarValidationException(['email' => 'A user with that email or mobile has already been registered.']);
        }

        $this->registrar->validate($request, $existing, [
            'first_name' => 'required',
            'birthdate' => 'required|date',
            'password' => 'required|confirmed|min:6',
        ]);

        // Register and login the user.
        $user = $this->registrar->register($request->except(User::$internal), $existing);
        $this->auth->guard('web')->login($user, true);

        return redirect()->intended('/');
    }
}