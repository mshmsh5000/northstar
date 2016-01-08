<?php

use Northstar\Models\ApiKey;

class ApiKeyTest extends TestCase
{
    protected $adminScope;

    protected $userScope;

    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        $this->artisan('migrate');
        $this->seed();

        $this->adminScope = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
        ];

        $this->userScope = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '123',
            'HTTP_X-DS-REST-API-Key' => '5464utyrs',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
        ];
    }

    /**
     * Test authentication & functionality of key index endpoint.
     * @test
     */
    public function testIndex()
    {
        // Verify a "user" scoped key is not able to list keys
        $response = $this->call('GET', 'v1/keys', [], [], [], $this->userScope);
        $this->assertEquals(403, $response->getStatusCode());

        // Verify an admin key is able to view all keys
        $response = $this->call('GET', 'v1/keys', [], [], [], $this->adminScope);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, json_decode($response->getContent())->data);
    }

    /**
     * Test authentication & functionality of key details endpoint.
     * @test
     */
    public function testShow()
    {
        // Verify a "user" scoped key is not able to see keys details
        $response = $this->call('GET', 'v1/keys/abc4324', [], [], [], $this->userScope);
        $this->assertEquals(403, $response->getStatusCode());

        // Verify a "user" scoped key is not able to see whether a key exists or not
        $response = $this->call('GET', 'v1/keys/notarealkey', [], [], [], $this->userScope);
        $this->assertEquals(403, $response->getStatusCode());

        // Verify an admin key is able to view key details
        $response = $this->call('GET', 'v1/keys/abc4324', [], [], [], $this->adminScope);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent())->data;
        $this->assertObjectHasAttribute('app_id', $data);
        $this->assertObjectHasAttribute('api_key', $data);
        $this->assertObjectHasAttribute('scope', $data);
    }

    /**
     * Test authentication & functionality of key creation endpoint.
     * @test
     */
    public function testUpdate()
    {
        // Verify a "user" scoped key is not able to update keys
        $response = $this->call('PUT', 'v1/keys/5464utyrs', [], [], [], $this->userScope, json_encode([
            'scope' => [
                'admin',
                'user',
            ],
        ]));
        $this->assertEquals(403, $response->getStatusCode());

        // Verify an admin key is able to update a key
        $response = $this->call('PUT', 'v1/keys/5464utyrs', [], [], [], $this->adminScope, json_encode([
            'scope' => [
                'admin',
                'user',
            ],
        ]));
        $this->assertEquals(200, $response->getStatusCode());

        $key = ApiKey::where('api_key', '5464utyrs')->firstOrFail();
        $this->assertEquals($key->scope, ['admin', 'user']);
    }

    /**
     * Test authentication & functionality of key deletion endpoint.
     * @test
     */
    public function testDestroy()
    {
        // Verify a "user" scoped key is not able to delete keys
        $response = $this->call('DELETE', 'v1/keys/5464utyrs', [], [], [], $this->userScope);
        $this->assertEquals(403, $response->getStatusCode());

        // Verify an admin key is able to delete a key
        $response = $this->call('DELETE', 'v1/keys/5464utyrs', [], [], [], $this->adminScope);
        $this->assertEquals(200, $response->getStatusCode());

        $key = ApiKey::where('api_key', '5464utyrs')->exists();
        $this->assertFalse($key);
    }
}
