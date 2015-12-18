<?php

use Northstar\Models\Token;
use Northstar\Models\User;

class AuthTest extends TestCase
{
    /**
     * Migrate database and set up HTTP headers
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        $this->seed();

        $this->server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
        ];

        $this->serverForParseTest = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz1=',
        ];

        $this->serverForParseTest2 = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz2=',
        ];

        $this->serverMissingToken = [
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
        ];

        $this->serverFakeToken = [
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'thisisafaketoken',
        ];

        $this->serverDrupalPasswordChecker = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
        ];
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

        $response = $this->call('POST', 'v1/login', [], [], [], $this->server, json_encode($credentials));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 Created status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should include user ID & session token
        $this->assertArrayHasKey('_id', $data['data']);
        $this->assertArrayHasKey('session_token', $data['data']);

        // Assert token exists in database
        $tokenCount = Token::where('key', '=', $data['data']['session_token'])->count();
        $this->assertEquals($tokenCount, 1);
    }

    /**
     * Test for logging out a user
     * POST /logout
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->call('POST', 'v1/logout', [], [], [], $this->server);
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

        $logoutResponse = $this->call('POST', 'v1/logout', [], [], [], $this->serverForParseTest, json_encode($payload));

        // The response should return a 200 Created status code
        $this->assertEquals(200, $logoutResponse->getStatusCode());

        // Verify parse_installation_ids got removed from the user
        $getResponse = $this->call('GET', 'v1/users/_id/bf1039b0271bcc636aa5477c', [], [], [], $this->serverForParseTest2);
        $getContent = $getResponse->getContent();
        $user = json_decode($getContent, true);

        $this->assertEquals(200, $getResponse->getStatusCode());
        $this->assertEquals(0, count($user['data'][0]['parse_installation_ids']));
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is given no token.
     */
    public function testMissingToken()
    {
        $response = $this->call('GET', 'v1/user/campaigns/123', [], [], [], $this->serverMissingToken);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is given a fake token.
     */
    public function testFakeToken()
    {
        $response = $this->call('GET', 'v1/user/campaigns/123', [], [], [], $this->serverFakeToken);

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

        $response = $this->call('POST', 'v1/login', [], [], [], $this->serverDrupalPasswordChecker, json_encode($credentials));
        $content = $response->getContent();
        $data = json_decode($content, true);
        $user = User::find('5430e850dt8hbc541c37cal3');

        // Assert reponse is 200 and has expected data
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($credentials['email'], $data['data']['email']);
        $this->assertEquals(null, $user->drupal_password);
        $this->assertArrayHasKey('password', $user['attributes']);

        // Response should include user ID & session token
        $this->assertArrayHasKey('_id', $data['data']);
        $this->assertArrayHasKey('session_token', $data['data']);

        // Assert token exists in database
        $tokenCount = Token::where('key', '=', $data['data']['session_token'])->count();
        $this->assertEquals($tokenCount, 1);
    }

    /**
     * Tests GET /users/{term}/{id} endpoint works correctly
     */
    public function testRetrieveUser()
    {
        // User info
        $user = User::find('5430e850dt8hbc541c37tt3d');

        // GET /users/_id/<user_id>
        $response = $this->call('GET', 'v1/users/_id/'.$user->_id, [], [], [], $this->serverMissingToken);
        $content = $response->getContent();
        $data = json_decode($content, true);

        // Assert response is 200 and has expected data
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('_id', $data['data'][0]);

        // GET /users/mobile/<mobile>
        $response = $this->call('GET', 'v1/users/mobile/'.$user->mobile, [], [], [], $this->serverMissingToken);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('mobile', $data['data'][0]);

        // GET /users/email/<email>
        $response = $this->call('GET', 'v1/users/email/'.$user->email, [], [], [], $this->serverMissingToken);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('email', $data['data'][0]);

        // GET /users/drupal_id/<drupal_id>
        $response = $this->call('GET', 'v1/users/drupal_id/'.$user->drupal_id, [], [], [], $this->serverMissingToken);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('drupal_id', $data['data'][0]);
    }
}
