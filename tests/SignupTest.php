<?php

use Northstar\Models\User;
use Northstar\Services\Phoenix;

class SignupTest extends TestCase
{
    /**
     * Test for retrieving a user's campaigns
     * GET /:signups
     *
     * @return void
     */
    public function testSignupIndex()
    {
        $user = User::create(['drupal_id' => '100001', 'first_name' => 'Chloe']);
        $user2 = User::create(['drupal_id' => '100002', 'first_name' => 'Dave']);

        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignupIndex')->with(['users' => ['100001', '100002']])->once()->andReturn([
            'data' => [
                [
                    'id' => '243',
                    'user' => [
                        'drupal_id' => '100001',
                    ]
                ],
                [
                    'id' => '44',
                    'user' => [
                        'drupal_id' => '100002',
                    ]
                ],
            ],
        ]);

        $this->asUser($user)->withScopes(['user'])->get('v1/signups?users='.$user->_id.','.$user2->_id);

        $this->assertResponseStatus(200);
        $this->seeJson();

        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'user' => [
                        'id', 'first_name', 'last_initial', 'photo', 'country',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Test for retrieving a user's campaigns
     * GET /:signups
     *
     * @return void
     */
    public function testSignupIndexWherePhoenixDoesntGiveDrupalId()
    {
        $user = User::create(['drupal_id' => '100001', 'first_name' => 'Chloe']);
        $user2 = User::create(['drupal_id' => '100002', 'first_name' => 'Dave']);

        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignupIndex')->with(['users' => ['100001', '100002']])->once()->andReturn([
            'data' => [
                [
                    'id' => '243',
                    // See! It doesn't give us that thing we expected it to! >:(
                ],
                [
                    'id' => '44',
                ],
            ],
        ]);

        // Let's just ensure it doesn't crash.
        $this->asUser($user)->withScopes(['user'])->get('v1/signups?users='.$user->_id.','.$user2->_id);
        $this->assertResponseStatus(200);
    }

    /**
     * Test to make sure user information is populated and returned correctly on index.
     * GET /:signups
     *
     * @return void
     */
    public function testSignupIndexUserInfo()
    {
        $user = User::create(['drupal_id' => '100003', 'first_name' => 'Name']);

        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignupIndex')->with(['users' => ['100003']])->once()->andReturn([
            'data' => [
                [
                    'user' => [
                        'drupal_id' => '100003',
                    ],
                ],
            ],
        ]);

        $response = $this->asUser($user)->withScopes(['user'])->get('v1/signups?users='.$user->_id);
        $this->assertResponseStatus(200);
        $this->seeJson();
        $json_decoded_response = $this->decodeResponseJson();

        $this->assertEquals('Name', $json_decoded_response['data'][0]['user']['first_name']);
    }

    /**
     * Test for retrieving a specific signup.
     * GET /signups/:signup_id
     *
     * @return void
     */
    public function testGetSignup()
    {
        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignup')->once()->andReturn([
            'data' => [
                'id' => '42',
                // ...
            ],
        ]);

        $this->get('v1/signups/123');

        // The response should return 200 OK & be valid JSON
        $this->assertResponseStatus(200);
        $this->seeJson();
    }

    /**
     * Test for submitting a campaign signup.
     * POST /signups
     *
     * @return void
     */
    public function testSubmitSignup()
    {
        $user = User::create(['drupal_id' => '123451']);

        // For testing, we'll mock a successful Phoenix API response.
        $mock = $this->mock(Phoenix::class);
        $mock->shouldReceive('createSignup')->with('123451', '123', 'test')->once()->andReturn(['1307']);
        $mock->shouldReceive('getSignup')->with('1307')->once()->andReturn([
            'data' => [
                'id' => '1307',
                // ...
            ],
        ]);

        // Make the request
        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/signups', [
            'campaign_id' => '123',
            'source' => 'test',
        ]);

        // The response should return 201 Created & be valid JSON
        $this->assertResponseStatus(201);
        $this->seeJson();
    }
}
