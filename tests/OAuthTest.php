<?php

use Northstar\Models\Client;
use Northstar\Models\User;

class OAuthTest extends TestCase
{
    /**
     * Test that the password grant provides a JWT for valid credentials.
     */
    public function testPasswordGrant()
    {
        $user = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
        ]);

        // Parse the token we received to see it's built correctly.
        $token = $this->decodeResponseJson()['access_token'];
        $jwt = (new \Lcobucci\JWT\Parser())->parse($token);

        // Check that the token has the expected user ID and scopes.
        $this->assertSame($user->id, $jwt->getClaim('sub'));
        $this->assertSame('user', $jwt->getClaim('role'));
        $this->assertSame(['admin', 'user'], $jwt->getClaim('scopes'));

        // Check that a refresh token was saved to the database.
        $this->seeInDatabase('refresh_tokens', [
            'user_id' => $user->id,
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * Test that the password grant provides a JWT for valid credentials.
     */
    public function testRoleClaim()
    {
        $admin = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret', 'role' => 'admin']);
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $admin->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        // Parse the token we received to see it's built correctly.
        $token = $this->decodeResponseJson()['access_token'];
        $jwt = (new \Lcobucci\JWT\Parser())->parse($token);
        $this->assertSame('admin', $jwt->getClaim('role'));
    }

    /**
     * Test that the password grant rejects invalid credentials.
     */
    public function testPasswordGrantWithInvalidCredentials()
    {
        $user = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'letmein',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that the client credentials grant rejects invalid credentials.
     */
    public function testClientCredentialsGrantWithFakeClient()
    {
        Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'totally_legit_client',
            'client_secret' => 'banana', // <-- not the real client secret
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that the client credentials grant will not return "trusted" clients
     * if the client_secret is not provided.
     */
    public function testClientCredentialsGrantWithMissingSecret()
    {
        Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that the client credentials grant rejects invalid credentials.
     */
    public function testClientCredentialsGrantWithInvalidCredentials()
    {
        Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
            'client_secret' => 'banana',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that clients can be granted a subset of their allowed scopes.
     */
    public function testRequestSubsetOfClientScopes()
    {
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
            'client_secret' => $client->client_secret,
            'scope' => 'user',
        ]);

        // We should receive a token with only the requested scopes.
        $jwt = (new \Lcobucci\JWT\Parser())->parse($this->decodeResponseJson()['access_token']);
        $this->assertSame(['user'], $jwt->getClaim('scopes'));
    }

    /**
     * Test that clients cannot be granted a scope that hasn't been
     * whitelisted for that client.
     */
    public function testCantRequestDisallowedClientScope()
    {
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
            'client_secret' => $client->client_secret,
            'scope' => 'user admin',
        ]);

        // We should receive a token, but *not* with the disallowed scope
        $jwt = (new \Lcobucci\JWT\Parser())->parse($this->decodeResponseJson()['access_token']);
        $this->assertSame(['user'], $jwt->getClaim('scopes'));
    }

    /**
     * Test that clients cannot be granted a scope that doesn't exist.
     */
    public function testCantRequestFakeClientScope()
    {
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => 'phpunit',
            'client_secret' => $client->client_secret,
            'scope' => 'dog',
        ]);

        $this->assertResponseStatus(400);
    }

    /**
     * Test that the client credentials grant provides a JWT for valid credentials.
     */
    public function testClientCredentials()
    {
        $client = Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
        ]);

        $jwt = (new \Lcobucci\JWT\Parser())->parse($this->decodeResponseJson()['access_token']);

        // Check that the token has the expected user ID and scopes.
        $this->assertSame('', $jwt->getClaim('sub'));
        $this->assertSame('', $jwt->getClaim('role'));
        $this->assertSame([], $jwt->getClaim('scopes'));
    }

    /**
     * Test that the refresh token grant provides a new JWT in exchange for
     * an unused refresh token, and then invalidates that refresh token.
     */
    public function testRefreshTokenGrant()
    {
        $user = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit']);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
        ]);

        // Get the provided refresh token.
        $refreshToken = $this->decodeResponseJson()['refresh_token'];

        // Get a new access token using the refresh token.
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'refresh_token' => $refreshToken,
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'token_type',
            'expires_in',
            'access_token',
            'refresh_token',
        ]);

        // And now, verify that that refresh token has been consumed.
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'refresh_token' => $refreshToken,
        ]);

        $this->assertResponseStatus(400);
    }

    /**
     * Test that an access token can be used to access a protected route.
     */
    public function testAccessToken()
    {
        $user = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        $token = $this->decodeResponseJson()['access_token'];

        $this->get('v1/users', ['Authorization' => 'Bearer '.$token]);
        $this->assertResponseStatus(200);
    }

    /**
     * Test that a refresh token can be revoked.
     */
    public function testRevokeRefreshToken()
    {
        $user = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ]);

        $jwt = $this->decodeResponseJson();

        // Now, delete that refresh token.
        $this->delete('v2/auth/token', [
            'token' => $jwt['refresh_token'],
        ], [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer '.$jwt['access_token'],
        ]);
        $this->assertResponseStatus(200);

        // And that token should now be rejected if provided:
        $this->post('v2/auth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => 'admin user',
            'refresh_token' => $jwt['refresh_token'],
        ]);
        $this->assertResponseStatus(400);
    }

    /**
     * Test that a made up refresh token does not cause an error.
     */
    public function testCantRevokeAnotherUsersRefreshToken()
    {
        $user1 = User::create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);
        $user2 = User::create(['email' => 'evil-user@dosomething.org', 'password' => 'secret']);
        $client = Client::create(['app_id' => 'phpunit', 'scope' => ['admin', 'user']]);

        // Make token for user #1.
        $jwt1 = $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user1->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ])->decodeResponseJson();

        // Hacks. OAuth server seems to get mad if more than one request is made per request.
        $this->refreshApplication();

        // Make token for user #2.
        $jwt2 = $this->post('v2/auth/token', [
            'grant_type' => 'password',
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'username' => $user2->email,
            'password' => 'secret',
            'scope' => 'admin user',
        ])->decodeResponseJson();

        // Now, try to delete User #1's refresh token w/ User #2's access token.
        $this->delete('v2/auth/token', [
            'token' => $jwt1['refresh_token'], // <--- User #1's refresh token
        ], [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Bearer '.$jwt2['access_token'], // <-- but User #2's access token!
        ]);
        $this->assertResponseStatus(401);
    }
}
