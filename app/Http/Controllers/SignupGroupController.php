<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SignupGroupController extends Controller
{
    /**
     * Phoenix Drupal API wrapper.
     * @var Phoenix
     */
    protected $phoenix;

    public function __construct(Phoenix $phoenix)
    {
        $this->phoenix = $phoenix;

        $this->middleware('key:admin');
    }

    /**
     * Return the users in multiple groups.
     *
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $response = [];

        if ($request->has('ids')) {
            $group_ids = explode(',', $request->input('ids'));

            foreach ($group_ids as $group_id) {
                $group = $this->getGroup($group_id);
                if (! empty($group)) {
                    $response[] = $group;
                }
            }
        } else {
            throw new BadRequestHttpException('Missing ids query parameter.');
        }

        return $this->respond($response);
    }

    /**
     * Display the users who share the specified signup group id.
     * GET /signup-group/:id
     *
     * @param int $id - Signup Group ID
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->getGroup($id);

        return $this->respond($response);
    }

    /**
     * Helper function to query for and return group information by signup_group id.
     *
     * @param string $id - Signup Group ID
     *
     * @return array
     */
    private function getGroup($id)
    {
        // signup_id and signup_group are saved as numbers
        $group = User::group($id);

        // Get the campaign id associated with the signup group ID
        $campaign_id = null;
        for ($i = 0; count($group) > 0 && $i < count($group[0]->campaigns); $i++) {
            $campaign = $group[0]->campaigns[$i];
            if ($campaign->signup_id == $id || $campaign->signup_group == $id) {
                $campaign_id = $campaign->drupal_id;
                break;
            }
        }

        foreach ($group as &$user) {
            if (isset($user->campaigns)) {
                for ($i = 0; $i < count($user->campaigns); $i++) {
                    // Cull campaigns that aren't associated with this group
                    if ($user->campaigns[$i]->drupal_id != $campaign_id) {
                        unset($user->campaigns[$i]);
                        continue;
                    }

                    if (isset($user->campaigns[$i]->reportback_id)) {
                        // get reportback data from drupal
                        $rbResponse = $this->phoenix->reportbackContent($user->campaigns[$i]->reportback_id);
                        $user->campaigns[$i]->reportback_data = $rbResponse['data'];
                    }
                }
            }
        }

        $response = [
            'signup_group' => $id,
            'campaign_id' => $campaign_id,
            'users' => $group,
        ];

        return $response;
    }
}
