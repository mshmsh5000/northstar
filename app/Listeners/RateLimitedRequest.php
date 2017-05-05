<?php

namespace Northstar\Listeners;

use DoSomething\StatHat\Client as StatHat;

class RateLimitedRequest
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
     * @return void
     */
    public function handle()
    {
        $this->stathat->ezCount('rate-limited user authentication attempt');
    }
}
