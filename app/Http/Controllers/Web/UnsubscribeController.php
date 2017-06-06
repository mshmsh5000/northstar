<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
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
        // make sure to check that inputs exist.
        dd($request->input('competition'));
    }
}
