<?php

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
    public function testStore()
    {
        // Verify a "user" scoped key is not able to list keys
        $response = $this->call('POST', 'v1/keys', [], [], [], $this->userScope, json_encode([
            'app_name' => 'test',
        ]));
        $this->assertEquals(403, $response->getStatusCode());

        // Verify an admin key is able to create a new key
        $response = $this->call('POST', 'v1/keys', [], [], [], $this->adminScope, json_encode([
            'app_name' => 'test',
        ]));
        $this->assertEquals(201, $response->getStatusCode());
    }
}
