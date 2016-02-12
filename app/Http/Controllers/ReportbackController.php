<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Auth;

class ReportbackController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Make a new Reportback controller, inject dependencies,
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
     * Displays the authenticated user's reportbacks.
     * GET /reportbacks
     *
     * @see Phoenix /api/v1/reportbacks endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-reportback-collection>
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $options = $request->query();

        // If a user is specified, turn Northstar ID into Drupal ID
        if (! empty($options['user'])) {
            $options['user'] = User::drupalIDForNorthstarId($options['user']);
        }

        return $this->phoenix->getReportbackIndex($options);
    }

    /**
     * Displays the currently authenticated user's reportbacks.
     * GET /profile/reportbacks
     *
     * @see Phoenix /api/v1/reportbacks endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-reportback-collection>
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

        return $this->phoenix->getReportbackIndex(['user' => $user->drupal_id]);
    }

    /**
     * Display the specified reportback.
     * GET /reportbacks/:reportback_id
     *
     * @see Phoenix /api/v1/reportbacks/:id endpoint
     * <https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-reportback>
     *
     * @param int $reportback_id - Reportback ID
     * @return \Illuminate\Http\Response
     */
    public function show($reportback_id)
    {
        return $this->phoenix->getReportback($reportback_id);
    }

    /**
     * Create a new reportback.
     * POST /reportbacks
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
            'quantity' => 'required|integer',
            'why_participated' => 'required',
            'file' => 'required|string', // Data URL!
            'caption' => 'string',
            'source' => 'string',
        ]);

        // Get the currently authenticated Northstar user.
        $user = Auth::user();

        // Return an error if the user doesn't exist on Phoenix.
        if (! $user->drupal_id) {
            throw new HttpException(401, 'The user must have a Drupal ID to submit a reportback.');
        }

        return $this->phoenix->createReportback($user->drupal_id, $request->input('campaign_id'), $request->except('campaign_id'));
    }
}
