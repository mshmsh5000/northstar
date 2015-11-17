<?php

namespace Northstar\Listeners;

use Northstar\Events\UserReportedBack;
use Northstar\Models\User;
use Northstar\Services\Phoenix;
use Northstar\Services\Parse;

class SendReportbackPushNotification
{
    /**
     * Parse API wrapper.
     * @var Parse
     */
    protected $parse;

    /**
     * Phoenix Drupal API wrapper
     * @var Phoenix
     */
    protected $phoenix;

    /**
     * Create the event listener.
     * @param Parse $parse - Injected Parse API wrapper
     * @param Phoenix $phoenix - Injected Drupal API wrapper
     */
    public function __construct(Parse $parse, Phoenix $phoenix)
    {
        $this->parse = $parse;
        $this->phoenix = $phoenix;
    }

    /**
     * Handle the event.
     *
     * @param  UserReportedBack  $event
     * @return void
     */
    public function handle(UserReportedBack $event)
    {
        $pushes = $this->createPushData($event);
        foreach ($pushes as $push) {
            // Send notifications to the user's devices.
            $this->parse->sendPushNotification($push['installation_ids'], $push['data']);
        }
    }

    /**
     * Compile push data per user to send to Parse.
     *
     * @param UserReportedBack $event
     * @return array
     */
    public function createPushData(UserReportedBack $event)
    {
        if (! empty($event->campaign->signup_group)) {
            $group = User::group($event->campaign->signup_group);
        } else {
            $group = [];
        }

        // If group count is only 1, safe to assume the 1 user is the one who reported back
        if (count($group) <= 1) {
            return [];
        }

        // Get reportback content
        $reportback_response = $this->phoenix->reportbackContent($event->campaign->reportback_id);

        // Assuming the last item in this array is the latest reportback submitted
        $reportback_items = $reportback_response['data']['reportback_items']['total'];
        $latest_item = $reportback_response['data']['reportback_items']['data'][$reportback_items - 1];

        // Get campaign content
        $campaign_title = $reportback_response['data']['campaign']['title'];

        // Get the user first name and last initial
        $reportback_user = User::where('drupal_id', '=', $event->user->drupal_id)->first();
        $username = 'A member';

        if (! empty($reportback_user)) {
            if (! empty($reportback_user->first_name)) {
                $username = $reportback_user->first_name;
            }

            if (! empty($reportback_user->last_name)) {
                $username .= ' '.substr($reportback_user->last_name, 0, 1).'.';
            }
        }

        // Loop through the users in the group.
        $push_data = [];
        foreach ($group as $user) {
            $drupal_id = $user->drupal_id;
            // Check that this user is not the user that triggered the event.
            if ($drupal_id !== $event->user->drupal_id && ! empty($user->parse_installation_ids)) {

                // Message sent in the push notification
                $message = $username.' shared a photo in your '.$campaign_title.' group.';

                // @TODO group.data.users doesn't include detailed reportback info for each user, is that ok?
                $data = [
                    'alert' => $message,
                    'extras' => [
                        'completion' => [
                            'message' => $message,
                            'group' => [
                                'data' => [
                                    'campaign_id' => $event->campaign->drupal_id,
                                    'users' => $group,
                                ],
                            ],
                            'reportback_items' => [
                                'total' => 1,
                                'data' => [$latest_item],
                            ],
                        ],
                    ],
                ];

                $push_data[] = [
                    'installation_ids' => $user->parse_installation_ids,
                    'data' => $data,
                ];
            }
        }

        return $push_data;
    }
}
