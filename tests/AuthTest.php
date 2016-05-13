<?php

use Northstar\Models\User;

class AuthTest extends TestCase
{
    /**
     * Test for logging in a user by username.
     * POST /auth/token
     *
     * @return void
     */
    public function testLoginByUsername()
    {
        // Create user to attempt to log in as.
        User::create([
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
        ]);

        // Test logging in with bogus info
        $this->withScopes(['user'])->json('POST', 'v1/auth/token', [
            'username' => 'login-test@dosomething.org',
            'password' => 'letmein',
        ]);
        $this->assertResponseStatus(401);

        // Test with the right credentials
        $this->withScopes(['user'])->json('POST', 'v1/auth/token', [
            'username' => 'login-test@dosomething.org',
            'password' => 'secret',
        ]);
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
     * Test for logging in a user by email.
     * POST /auth/token
     *
     * @return void
     */
    public function testLoginByEmail()
    {
        $credentials = [
            'email' => 'login-test@dosomething.org',
            'password' => 'secret',
        ];

        // Create user to attempt to log in as.
        User::create($credentials);

        // Test logging in with bogus info
        $this->withScopes(['user'])->json('POST', 'v1/auth/token', [
            'email' => 'login-test@dosomething.org',
            'password' => 'letmein',
        ]);
        $this->assertResponseStatus(401);

        // Test with the right credentials
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
     * Test for logging in a user by mobile.
     * POST /auth/token
     *
     * @return void
     */
    public function testLoginByMobile()
    {
        // Create user to attempt to log in as.
        User::create([
            'mobile' => '5551234455',
            'password' => 'secret',
        ]);

        $this->withScopes(['user'])->json('POST', 'v1/auth/token', [
            'mobile' => '(555) 123-4455',
            'password' => 'secret',
        ]);

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
     * POST /auth/verify
     *
     * @return void
     */
    public function testVerify()
    {
        User::create([
            'email' => 'verify-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'verify-test@dosomething.org',
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
     * Test for logging in a user, but wildly!
     * POST /auth/verify
     *
     * @return void
     */
    public function testNormalizedVerify()
    {
        User::create([
            'email' => 'normalized-verify@dosomething.org',
            'password' => 'secret',
        ]);

        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'Normalized-Verify@dosomething.org ', // <-- a trailing space!? the nerve!
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
     * Test that you can't register a user without an email or mobile.
     * POST /auth/register
     *
     * @return void
     */
    public function testIncompleteRegistration()
    {
        $this->withScopes(['user'])->json('POST', 'v1/auth/register', [
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(422);
    }

    /**
     * Test for registering in a user
     * POST /auth/register
     *
     * @return void
     */
    public function testRegister()
    {
        $this->withScopes(['user'])->json('POST', 'v1/auth/register', [
            'email' => 'test-registration@dosomething.org',
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(200);
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
    }

    /**
     * Test that you can't register a duplicate user.
     * POST /auth/register
     *
     * @return void
     */
    public function testRegisterDuplicate()
    {
        User::create([
            'email' => 'fn-2187@first-order.mil',
        ]);

        // Try to register an account that already exists, but with different capitalization
        $this->withScopes(['user'])->json('POST', 'v1/auth/register', [
            'email' => 'FN-2187@First-Order.mil',
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can register a user with a crazy long email.
     */
    public function testRegisterLongEmail()
    {
        $this->withScopes(['user'])->json('POST', 'v1/auth/register', [
            'email' => 'loremipsumdolorsitametconsecteturadipiscingelitduisut1234567890b@example.com',
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(200);
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
