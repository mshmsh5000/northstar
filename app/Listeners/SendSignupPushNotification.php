<?php

namespace Northstar\Listeners;

use Northstar\Events\UserSignedUp;
use Northstar\Models\User;
use Northstar\Services\Parse;

class SendSignupPushNotification
{
    /**
     * Parse API wrapper.
     * @var Parse
     */
    protected $parse;

    /**
     * Create the event listener.
     * @param Parse $parse - Injected Parse API wrapper
     */
    public function __construct(Parse $parse)
    {
        $this->parse = $parse;
    }

    /**
     * Handle the event.
     *
     * @param  UserSignedUp  $event
     * @return void
     */
    public function handle(UserSignedUp $event)
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
     * @param  UserSignedUp  $event
     * @return array
     */
    public function createPushData(UserSignedUp $event)
    {
        if (! empty($event->campaign->signup_group)) {
            $group = User::group($event->campaign->signup_group);
        } else {
            $group = [];
        }

        // If group count is only 1, safe to assume this user is signing up to a group.
        if (count($group) <= 1) {
            return [];
        }

        // Get the user first name and last initial
        $signup_user = User::where('drupal_id', '=', $event->user->drupal_id)->first();
        $username = 'A member';

        if (! empty($signup_user)) {
            if (! empty($signup_user->first_name)) {
                $username = $signup_user->first_name;
            }

            if (! empty($signup_user->last_name)) {
                $username .= ' '.substr($signup_user->last_name, 0, 1).'.';
            }
        }

        // Loop through the users in the group.
        $push_data = [];
        foreach ($group as $user) {
            $drupal_id = $user->drupal_id;
            // Check that this user is not the user that triggered the event.
            if ($drupal_id !== $event->user->drupal_id && ! empty($user->parse_installation_ids)) {

                // Message sent in the push notification
                $message = $username.' joined your group!';

                // @TODO group.data.users doesn't include detailed reportback info for each user, is that ok?
                $data = [
                    'alert' => $message,
                    'extras' => [
                        'invitation' => [
                            'message' => $message,
                            'group' => [
                                'data' => [
                                    'campaign_id' => $event->campaign->drupal_id,
                                    'users' => $group,
                                ],
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
