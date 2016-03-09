<?php

use Northstar\Models\Token;
use Northstar\Models\User;

class AuthTest extends TestCase
{
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
                        'id',
                    ],
                ],
            ],
        ]);

        // Assert token given in the response also exists in database
        $this->seeInDatabase('tokens', [
            'key' => $this->decodeResponseJson()['data']['key'],
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
        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'test@dosomething.org',
            'password' => 'secret',
        ]);

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
            ],
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
            'Authorization' => 'Bearer any_token_anytime_anywhere',
        ]);

        $this->assertResponseStatus(401);
    }
}
