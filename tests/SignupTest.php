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
        $user = User::create(['drupal_id' => '100001']);
        $user2 = User::create(['drupal_id' => '100002']);

        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignupIndex')->with(['users' => ['100001', '100002']])->once()->andReturn([
            'data' => [
                [
                    'id' => '1',
                ],
                [
                    'id' => '2',
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
