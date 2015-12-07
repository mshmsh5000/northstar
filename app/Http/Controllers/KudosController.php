<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Services\Phoenix;
use Northstar\Models\User;
use Northstar\Events\UserGotKudo;

class KudosController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->middleware('key:user');
        $this->middleware('auth');
    }

    /**
     * Store a new kudos from a user.
     * Kudos request made from mobile app and forwarded to Northstar.
     * Northstar finds the drupal user and sends request on to Drupal.
     * POST /kudos
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $user = User::current();

        $drupal_id = $user->drupal_id;

        $response = $this->phoenix->storeKudos($drupal_id, $request);

        // Fire kudo event.
        event(new UserGotKudo($request->reportback_item_id));

        return $this->respond($response);
    }
}
