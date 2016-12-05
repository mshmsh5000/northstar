<?php

use Northstar\Models\Client;

class HelpersTest extends TestCase
{
    /** @test */
    public function testRouteHasAttachedMiddleware()
    {
        $this->get('/login');

        // It should be able to check if in a middleware group.
        $this->assertTrue(has_middleware('web'));
        $this->assertFalse(has_middleware('api'));

        // ...or just if it has any middleware at all!
        $this->assertTrue(has_middleware());
    }

    /** @test */
    public function testGetClientId()
    {
        // Web requests should report as 'northstar'.
        $this->get('/register');
        $this->assertEquals(client_id(), 'northstar');

        // Make a request using legacy API header.
        $client = Client::create(['client_id' => 'legacy_client', 'scope' => 'user']);
        $this->withLegacyApiKey($client)->json('POST', '/v1/auth/register', [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ]);
        $this->assertEquals(client_id(), 'legacy_client');

        // And make a request using OAuth.
        $this->asNormalUser()->json('POST', '/v1/auth/register', [
            'email' => $this->faker->safeEmail,
            'password' => $this->faker->password,
        ]);
        $this->assertEquals(client_id(), 'phpunit');
    }
}
