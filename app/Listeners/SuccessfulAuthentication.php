<?php

namespace Northstar\Listeners;

use Carbon\Carbon;
use DoSomething\StatHat\Client as StatHat;
use Illuminate\Auth\Events\Login;
use Northstar\Models\User;

class SuccessfulAuthentication
{
    /**
     * The StatHat client.
     *
     * @var StatHat
     */
    protected $stathat;

    /**
     * Create the event listener.
     *
     * @param StatHat $stathat
     */
    public function __construct(StatHat $stathat)
    {
        $this->stathat = $stathat;
    }

    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     * @internal param User $user
     */
    public function handle(Login $event)
    {
        /** @var User $user */
        $user = $event->user;

        // Update the user's 'last_logged_in' field.
        $user->last_authenticated_at = Carbon::now();
        $user->save();

        // Update counter in StatHat
        $this->stathat->ezCount('user authentication');
    }
}
