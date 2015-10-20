<?php

namespace Northstar\Listeners;

use Northstar\Events\UserGotKudo;
use Northstar\Models\User;
use Northstar\Services\DrupalAPI;
use Northstar\Services\Parse;


class SendKudoPushNotification {

    /**
    * Parse API wrapper.
    * @var Parse
    */
    protected $parse;

    /**
     * Drupal API wrapper
     * @var DrupalAPI
     */
    protected $drupal;

    /**
     * Create the event listener.
     * @param Parse $parse - Injected Parse API wrapper
     * @param DrupalAPI $drupal - Injected Drupal API wrapper
     */
    public function __construct(Parse $parse, DrupalAPI $drupal)
    {
        $this->parse = $parse;
        $this->drupal = $drupal;
    }

    /**
     * Handle the event.
     *
     * @param  UserGotKudo $event
     * @return void
     */
    public function handle(UserGotKudo $event)
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
     * @param UserGotKudo $event
     * @return array
     */
    public function createPushData(UserGotKudo $event)
    {
        // Get reportback item content that received the kudo.
        $reportback_response = $this->drupal->reportbackItemContent($event->reportback_item_id);

        // Get the user who owns that reportback item.
        $user = User::where('drupal_id', '=', $reportback_response['data'][0]['user']['id'])->first();

        if (empty($user->parse_installation_ids)) {
            return [];
        }

        // Message sent in the push notification
        $message = 'Your photo received a kudos!';

        // Build the push data object.
        $data = [
            'alert' => $message,
            'extras' => [
                'kudos' => [
                    'title' => 'Kudos',
                    'message' => $message,
                    'reportback_item' => [
                        'data' => $reportback_response,
                    ],
                ],
            ],
        ];

        $push_data[] = [
            'installation_ids' => $user->parse_installation_ids,
            'data' => $data,
        ];

        return $push_data;
    }
}
