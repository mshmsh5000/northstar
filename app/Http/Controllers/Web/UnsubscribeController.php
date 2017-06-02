<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Routing\Controller as BaseController;

class UnsubscribeController extends BaseController
{
    /**
     * Displays the subscriptions page
     */
    public function show()
    {
        return view('auth.subscriptions');
    }

    /**
     * Unsubscribes user from competition emails.
     */
    public function unsubscribeFromCompetition()
    {
        dd('hi');
    }
}
