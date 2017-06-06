<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use DoSomething\Gateway\Gladiator;
use Illuminate\Routing\Controller as BaseController;

class UnsubscribeController extends BaseController
{
    /**
     * Displays the subscriptions page
     */
    public function getSubscriptions()
    {
        return view('auth.subscriptions');
    }

    /**
     *
     */
    public function postSubscriptions(Request $request)
    {
        dd(app(Gladiator::class));
        // make sure to check that inputs exist.
        dd($request->input('competition'));
    }
}
