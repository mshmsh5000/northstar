<?php

use Northstar\Models\Token;
use Northstar\Models\User;

class AuthTest extends TestCase
{
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

        $this->withScopes(['user'])->json('POST', 'v1/auth/token', $credentials);
        $this->assertResponseStatus(201);
        $this->seeJsonStructure([
            'data' => [
                'key',
                'user' => [
                    'data' => [
                        'id'
                    ],
                ],
            ],
        ]);

        // Assert token given in the response also exists in database
        $this->seeInDatabase('tokens', [
            'key' => $this->decodeResponseJson()['data']['key']
        ]);
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

        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', $credentials);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    /**
     * Test for logging out a user
     * POST /logout
     *
     * @return void
     */
    public function testLogout()
    {
        $user = User::create(['first_name' => 'Puppet']);
        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/auth/invalidate');

        // Should return 200 with valid JSON status message
        $this->assertResponseStatus(200);
        $this->seeJson();
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
        $user = User::create([
            'first_name' => 'Puppet',
            'parse_installation_ids' => [
                'parse-abc123',
            ]
        ]);

        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/auth/invalidate', [
            'parse_installation_ids' => 'parse-abc123',
        ]);

        // The response should return a 200 OK status code
        $this->assertResponseStatus(200);

        // Verify parse_installation_ids got removed from the user
        $this->notSeeIndatabase('users', [
            '_id' => $user->_id,
            'parse_installation_ids' => ['parse-abc123'],
        ]);
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is called without a token in the Authorization header.
     */
    public function testMissingToken()
    {
        $this->withScopes(['user'])->get('v1/profile');
        $this->assertResponseStatus(401);
    }

    /**
     * Tests that a proper error is thrown when a route requiring an auth token
     * is given a fake token.
     */
    public function testFakeToken()
    {
        $this->withScopes(['user'])->get('v1/profile', [
            'Authorization' => 'Bearer any_token_anytime_anywhere'
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Tests that Drupal password hasher is working correctly.
     */
    public function testAuthenticatingWithDrupalPassword()
    {
        $user = User::create([
            'email' => 'dries.buytaert@example.com',
            'drupal_password' => '$S$DOQoztwlGzTeaobeBZKNzlDttbZscuCkkZPv8yeoEvrn26H/GN5b',
        ]);

        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);

        // Assert response is 200 OK and has expected data
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'email' => $user->email
            ],
        ]);

        // Assert user has been updated in the database with a newly hashed password.
        $user = $user->fresh();
        $this->assertArrayNotHasKey('drupal_password', $user['attributes']);
        $this->assertArrayHasKey('password', $user['attributes']);

        // Finally, let's try logging in with the newly hashed password
        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);
        $this->assertResponseStatus(200);
    }
}
