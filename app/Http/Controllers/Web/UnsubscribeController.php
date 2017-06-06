<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use DoSomething\Gateway\Gladiator;
use Illuminate\Routing\Controller as BaseController;

class UnsubscribeController extends BaseController
{
    /**
     * Gladiator instance.
     *
     * @var \DoSomething\Gateway\Gladiator
     */
    protected $gladiator;

    /**
     * Create the controller instance.
     *
     * @param \DoSomething\Gateway\Gladiator $gladiator
     */
    public function __construct(Gladiator $gladiator)
    {
        $this->gladiator = $gladiator;
    }

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
        dd($this->gladiator);
        // make sure to check that inputs exist.
        dd($request->input('competition'));
    }
}
