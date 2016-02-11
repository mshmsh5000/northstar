<?php

use Northstar\Models\User;
use Northstar\Services\Phoenix;

class SignupTest extends TestCase
{
    /**
     * Additional server variables for the request.
     *
     * @var array
     */
    protected $serverVariables = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_Accept' => 'application/json',
    ];

    /**
     * Headers for a typical user.
     * @var array
     */
    protected $server;

    /**
     * Headers for a user who has already signed up.
     * @var array
     */
    protected $signedUpServer;

    /**
     * Headers for a user who has already reported back.
     * @var array
     */
    protected $reportedBackServer;

    /**
     * Migrate database and set HTTP headers.
     */
    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        $this->artisan('migrate');
        $this->seed();

        // Prepare server headers
        $this->server = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('5430e850dt8hbc541c37tt3d')->login()->key,
        ];

        $this->signedUpServer = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('5480c950bffebc651c8b456f')->login()->key,
        ];

        $this->reportedBackServer = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('bf1039b0271bcc636aa5477a')->login()->key,
        ];
    }

    /**
     * Test for retrieving a user's campaigns
     * GET /:signups
     *
     * @return void
     */
    public function testSignupIndex()
    {
        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('getSignupIndex')->with(['user' => 12345])->once()->andReturn([
            'data' => [
                [
                    'id' => '243',
                    // ...
                ],
                [
                    'id' => '44',
                    // ...
                ],
            ],
        ]);

        $response = $this->call('GET', 'v1/signups?user=12345', [], [], [], $this->server);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
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

        $response = $this->call('GET', 'v1/signups/123', [], [], [], $this->signedUpServer);

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $content = $response->getContent();
        $this->assertJson($content);
    }

    /**
     * Test for submitting a campaign signup.
     * POST /signups
     *
     * @return void
     */
    public function testSubmitSignup()
    {
        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('createSignup')->once()->andReturn([
            '1307',
        ]);

        // Make the request
        $response = $this->call('POST', 'v1/signups', [], [], [], $this->server, json_encode([
            'campaign_id' => '123',
            'source' => 'test',
        ]));

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $content = $response->getContent();
        $this->assertJson($content);
    }
}
