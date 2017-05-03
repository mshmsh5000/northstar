<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Routing\Controller as BaseController;

class UnsubscribeController extends BaseController
{
    /**
     * Displays the subscriptions page
     */
    public function unsubscribe()
    {
        return view('auth.subscriptions');
    }
}
