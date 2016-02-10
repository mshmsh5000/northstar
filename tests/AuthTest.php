<?php

use Northstar\Models\Token;
use Northstar\Models\User;

class AuthTest extends TestCase
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
     * Headers for a user-scoped API key.
     * @var array
     */
    protected $server = [
        'HTTP_X-DS-REST-API-Key' => 'abc4324',
    ];

    /**
     * Headers for a user-scoped API key and authentication token.
     * @var array
     */
    protected $loggedInServer = [
        'HTTP_X-DS-REST-API-Key' => 'abc4324',
        'HTTP_Authorization' => 'Bearer S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz1=',
    ];

    /**
     * Headers for a user-scoped API key and fake token.
     * @var array
     */
    protected $serverFakeToken = [
        'HTTP_X-DS-REST-API-Key' => 'abc4324',
        'HTTP_Authorization' => 'Bearer thisisafaketoken',
    ];

    /**
     * Migrate database and set up HTTP headers
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
        $this->seed();
    }

    /**
     * Test for logging in a user
     * POST /login
     *
     * @return void
     */
    public function testLogin()
    {
        // User login info
        $credentials = [
            'email' => 'test@dosomething.org',
            'password' => 'secret',
        ];

        $response = $this->call('POST', 'v1/auth/token', [], [], [], $this->server, json_encode($credentials));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 201 Created status code
        $this->assertEquals(201, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should include user ID & authentication token
        $this->assertArrayHasKey('user', $data['data']);
        $this->assertArrayHasKey('_id', $data['data']['user']['data']);
        $this->assertArrayHasKey('key', $data['data']);

        // Assert token exists in database
        $tokenCount = Token::where('key', '=', $data['data']['key'])->count();
        $this->assertEquals($tokenCount, 1);
    }

    /**
     * Test for logging in a user
     * POST /login
     *
     * @return void
     */
    public function testVerify()
    {
        // User login info
        $credentials = [
            'email' => 'test@dosomething.org',
            'password' => 'secret',
        ];

        $response = $this->call('POST', 'v1/auth/verify', [], [], [], $this->server, json_encode($credentials));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 Okay status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON, & include user data
        $this->assertJson($content);
        $this->assertArrayHasKey('_id', $data['data']);
    }

    /**
     * Test for logging out a user
     * POST /logout
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->call('POST', 'v1/auth/invalidate', [], [], [], $this->loggedInServer);
        $content = $response->getContent();

        // The response should return a 200 Created status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
    }

    /**
     * Tests that when a user gets logged out, we can also remove the
     * Parse installation id from the user doc.
     * POST /logout
     *
     * @return void
     */
    public function testLogoutRemovesParseInstallationIds()
    {
        $payload = [
            'parse_installation_ids' => 'parse-abc123',
        ];

        $logoutResponse = $this->call('POST', 'v1/auth/invalidate', [], [], [], $this->loggedInServer, json_encode($payload));

        // The response should return a 200 OK status code
        $this->assertEquals(200, $logoutResponse->getStatusCode());

        // Verify parse_installation_ids got removed from the user
        $user = User::find('bf1039b0271bcc636aa5477c');
        $this->assertEquals(0, count($user->parse_installation_ids));
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is given no token.
     */
    public function testMissingToken()
    {
        $response = $this->call('GET', 'v1/profile', [], [], [], $this->server);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is given a fake token.
     */
    public function testFakeToken()
    {
        $response = $this->call('GET', 'v1/profile', [], [], [], $this->serverFakeToken);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Tests that drupal password checker is working correctly.
     */
    public function testDrupalPasswordChecker()
    {
        // User login info
        $credentials = [
            'email' => 'test4@dosomething.org',
            'password' => 'secret',
        ];

        $response = $this->call('POST', 'v1/auth/token', [], [], [], $this->server, json_encode($credentials));
        $content = $response->getContent();
        $data = json_decode($content, true);
        $user = User::find('5430e850dt8hbc541c37cal3');

        // Assert response is 201 Created and has expected data
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals($credentials['email'], $data['data']['user']['data']['email']);
        $this->assertEquals(null, $user->drupal_password);
        $this->assertArrayHasKey('password', $user['attributes']);

        // Response should include user ID & authentication token
        $this->assertArrayHasKey('_id', $data['data']['user']['data']);
        $this->assertArrayHasKey('key', $data['data']);

        // Assert token exists in database
        $tokenCount = Token::where('key', '=', $data['data']['key'])->count();
        $this->assertEquals($tokenCount, 1);
    }
}
