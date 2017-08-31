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
     * Post unsubscribe requests to gladiator.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function postSubscriptions(Request $request)
    {
        $user = $request->input('user');
        $competition = $request->input('competition');

        if ($user && $competition) {
            try {
                $response = $this->gladiator->unsubscribeUser($user, $competition);

                return redirect()->back()->with('status', $response['message']);
            } catch (\Exception $e) {
                return redirect()->back()->with('status', 'There was an error processing this request, please try again later.');
            }
        }
    }
}
