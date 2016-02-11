<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Auth;

class SignupController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Make a new Signup controller, inject dependencies,
     * and set middleware for this controller's methods.
     * @param Phoenix $phoenix
     */
    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->middleware('key:user', ['only' => ['profile', 'store']]);
        $this->middleware('auth', ['only' => ['profile', 'store']]);
    }

    /**
     * Displays the (optionally filtered) index of signups.
     * GET /signups
     *
     * @see Phoenix /api/v1/signups endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-signup-collection>
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->phoenix->getSignupIndex($request->query());
    }

    /**
     * Displays the currently authenticated user's signups.
     * GET /profile/signups
     *
     * @see Phoenix /api/v1/signups endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-signup-collection>
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        // Get the currently authenticated Northstar user.
        $user = Auth::user();

        // Return an error if the user doesn't exist in Phoenix.
        if (! $user->drupal_id) {
            throw new HttpException(401, 'The user must have a Drupal ID to sign up for a campaign.');
        }

        return $this->phoenix->getSignupIndex(['user' => $user->drupal_id]);
    }

    /**
     * Display the specified signup.
     * GET /signups/:signup_id
     *
     * @see Phoenix /api/v1/signups/:sid endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-campaign-1>
     *
     * @param int $signup_id - Signup ID
     * @return \Illuminate\Http\Response
     */
    public function show($signup_id)
    {
        return $this->phoenix->getSignup($signup_id);
    }

    /**
     * Create a new signup.
     * POST /signups
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function store(Request $request)
    {
        // Validate endpoint parameters
        $this->validate($request, [
            'campaign_id' => 'required|integer',
            'source' => 'required',
        ]);

        // Get the currently authenticated Northstar user.
        $user = Auth::user();

        // Return an error if the user doesn't exist in Phoenix.
        if (! $user->drupal_id) {
            throw new HttpException(401, 'The user must have a Drupal ID to sign up for a campaign.');
        }

        return $this->phoenix->createSignup($user->drupal_id, $request->input('campaign_id'), $request->input('source'));
    }
}
