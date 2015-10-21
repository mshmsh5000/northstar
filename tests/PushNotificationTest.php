<?php

use Northstar\Events\UserReportedBack;
use Northstar\Events\UserSignedUp;
use Northstar\Events\UserGotKudo;
use Northstar\Listeners\SendReportbackPushNotification;
use Northstar\Listeners\SendSignupPushNotification;
use Northstar\Listeners\SendKudoPushNotification;
use Northstar\Models\User;
use Northstar\Models\Campaign;

class PushNotificationTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        Artisan::call('migrate');
        $this->seed();

        $this->drupalMock = $this->mock('Northstar\Services\DrupalAPI');
        $this->parseMock = $this->mock('Northstar\Services\Parse');
    }

    /**
     * Test for verifying the push data compiled before it's sent to the Parse
     * client.
     */
    public function testReportbackPushData()
    {
        // Simulated user who just reported back
        $user = new User();
        $user->drupal_id = '100006';

        // Simulated campaign info for user who just reported back
        $campaign = new Campaign();
        $campaign->signup_group = '200';
        $campaign->reportback_id = '1000';
        $campaign->drupal_id = '123';
        $event = new UserReportedBack($user, $campaign);

        // Response from server for the reportback just submitted by the user above
        $reportback_response = [
            'data' => [
                'reportback_items' => [
                    'total' => 1,
                    'data' => [
                        [
                            'id' => '1000',
                            'caption' => 'Test caption 1000.',
                            'uri' => 'http://www.example.com/reportback-items/1000',
                            'media' => [
                                'uri' => 'http://www.example.com/reportback-items/1001.jpg',
                                'type' => 'image'
                            ],
                            'created_at' => '1234567890',
                            'status' => 'approved'
                        ],
                    ],
                ],
                'campaign' => [
                    'title' => 'Test Campaign',
                ],
            ]
        ];

        $this->drupalMock->shouldReceive('reportbackContent')->once()->andReturn($reportback_response);
        $notification = new SendReportbackPushNotification($this->parseMock, $this->drupalMock);

        $pushes = $notification->createPushData($event);

        // Seeded table should be setup so that there's one other user in group
        // '200'. That user should be receiving a push notification.
        $this->assertCount(1, $pushes);
        $this->assertCount(1, $pushes[0]['installation_ids']);
        $this->assertEquals('parse-100', $pushes[0]['installation_ids'][0]);

        // Verify structure of the push data.
        $push_data = $pushes[0]['data'];
        $this->assertArrayHasKey('alert', $push_data);
        $this->assertArrayHasKey('extras', $push_data);
        $this->assertArrayHasKey('completion', $push_data['extras']);
        $completion = $push_data['extras']['completion'];
        $this->assertEquals($push_data['alert'], 'Push U. shared a photo in your Test Campaign group.');
        $this->assertEquals($push_data['alert'], $completion['message']);
        $this->assertArrayHasKey('message', $completion);
        $this->assertArrayHasKey('group', $completion);
        $this->assertArrayHasKey('reportback_items', $completion);

        // Verify reportback_item in push data matches the one in the event.
        $this->assertCount(1, $completion['reportback_items']['data']);
        $this->assertEquals($campaign->reportback_id, $completion['reportback_items']['data'][0]['id']);
    }

    /**
     * @group signupPushNotification
     *
     * Test for verifying the push data compiled before it's sent to the Parse
     * client.
     */
    public function testSignupPushData()
    {
        // Simulated user who just signed up
        $user = new User();
        $user->drupal_id = '100006';

        // Simulated campaign info for user who just reported back
        $campaign = new Campaign();
        $campaign->signup_group = '200';
        $campaign->drupal_id = '123';
        $event = new UserSignedUp($user, $campaign);

        $notification = new SendSignupPushNotification($this->parseMock);
        $pushes = $notification->createPushData($event);

        // Seeded table should be setup so that there's one other user in group
        // '200'. That user should be receiving a push notification.
        $this->assertCount(1, $pushes);
        $this->assertCount(1, $pushes[0]['installation_ids']);
        $this->assertEquals('parse-100', $pushes[0]['installation_ids'][0]);

        // Verify structure of the push data.
        $push_data = $pushes[0]['data'];
        $this->assertArrayHasKey('alert', $push_data);
        $this->assertArrayHasKey('extras', $push_data);
        $this->assertArrayHasKey('invitation', $push_data['extras']);
        $invitation = $push_data['extras']['invitation'];
        $this->assertEquals($push_data['alert'], 'Push U. joined your group!');
        $this->assertEquals($push_data['alert'], $invitation['message']);
        $this->assertArrayHasKey('message', $invitation);
        $this->assertArrayHasKey('group', $invitation);
    }

    /**
     * @group kudoPushNotification
     *
     * Test for verifying the push data compiled before it's sent to the Parse
     * client.
     */
    public function testKudoPushData()
    {
        // Fake reportback item id that got a kudo.
        $reportback_item_id = '1000';

        $event = new UserGotKudo($reportback_item_id);

        $reportback_response = [
            'data' => [
                [
                    'id' => '1000',
                    'status' => 'approved',
                    'caption' => 'Test caption 1000.',
                    'uri' => 'http://www.example.com/reportback-items/1000',
                    'media' => [
                        'uri' => 'http://www.example.com/reportback-items/1001.jpg',
                        'type' => 'image'
                    ],
                    'created_at' => '1234567890',
                    'user' => [
                        'id' => '100005'
                    ]
                ],
            ],
        ];

        $this->drupalMock->shouldReceive('reportbackItemContent')->once()->andReturn($reportback_response);

        $notification = new SendKudoPushNotification($this->parseMock, $this->drupalMock);
        $pushes = $notification->createPushData($event);

        $this->assertEquals('parse-100', $pushes[0]['installation_ids'][0]);

        // Verify structure of the push data.
        $push_data = $pushes[0]['data'];
        $this->assertArrayHasKey('alert', $push_data);
        $this->assertArrayHasKey('extras', $push_data);
        $this->assertArrayHasKey('kudos', $push_data['extras']);
        $kudos = $push_data['extras']['kudos'];
        $this->assertEquals($push_data['alert'], 'Your photo received a kudos!');
        $this->assertEquals($push_data['alert'], $kudos['message']);
        $this->assertArrayHasKey('message', $kudos);
        $this->assertArrayHasKey('reportback_item', $kudos);
    }
}
