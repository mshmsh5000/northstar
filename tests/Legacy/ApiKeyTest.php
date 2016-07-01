<?php

use Northstar\Models\Client;

class ApiKeyTest extends TestCase
{
    /**
     * Test authentication & functionality of key index endpoint.
     * @test
     */
    public function testIndex()
    {
        Client::create(['client_id' => 'test']);
        Client::create(['client_id' => 'testingz']);

        // Verify an admin key is able to view all keys
        $this->withLegacyApiKeyScopes(['admin'])->get('v1/keys');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'app_id', 'api_key', 'scope',
                ],
            ],
        ]);

        // Verify a "user" scoped key is not able to list keys
        $this->withLegacyApiKeyScopes(['user'])->get('v1/keys');
        $this->assertResponseStatus(403);
    }

    /**
     * Test authentication & functionality of key creation endpoint.
     * @test
     */
    public function testStore()
    {
        $attributes = [
            'app_id' => 'dog', // hello this is doge key
            'scope' => ['admin'],
        ];

        // Verify a "user" scoped key is not able to create new keys
        $this->withLegacyApiKeyScopes(['user'])->json('POST', 'v1/keys', $attributes);
        $this->assertResponseStatus(403);

        // Verify an admin key is able to create a new key
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/keys', $attributes);
        $this->assertResponseStatus(201);
        $this->seeJsonStructure([
            'data' => [
                'app_id', 'api_key', 'scope',
            ],
        ]);
    }

    /**
     * Test authentication & functionality of key details endpoint.
     * @test
     */
    public function testShow()
    {
        $client = Client::create(['client_id' => 'phpunit_key']);

        // Verify a "user" scoped key is not able to see keys details
        $this->withLegacyApiKeyScopes(['user'])->get('v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(403);

        // Verify a "user" scoped key is not able to see whether a key exists or not
        $this->withLegacyApiKeyScopes(['user'])->get('v1/keys/notarealkey');
        $this->assertResponseStatus(403);

        // Verify an admin key is able to view key details
        $this->withLegacyApiKeyScopes(['admin'])->get('v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(200);
    }

    /**
     * Test authentication & functionality of key creation endpoint.
     * @test
     */
    public function testUpdate()
    {
        $client = Client::create(['client_id' => 'update_key']);

        $modifications = [
            'scope' => [
                'admin',
                'user',
            ],
        ];

        // Verify a "user" scoped key is not able to update keys
        $this->withLegacyApiKeyScopes(['user'])->json('PUT', 'v1/keys/'.$client->client_secret, $modifications);
        $this->assertResponseStatus(403);

        // Verify an admin key is able to update a key
        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/keys/'.$client->client_secret, $modifications);
        $this->assertResponseStatus(200);
        $this->seeInDatabase('clients', [
            'client_id' => 'update_key',
            'scope' => ['admin', 'user'],
        ]);
    }

    /**
     * Test authentication & functionality of key deletion endpoint.
     * @test
     */
    public function testDestroy()
    {
        $client = Client::create(['client_id' => 'delete_me']);

        // Verify a "user" scoped key is not able to delete keys
        $this->withLegacyApiKeyScopes(['user'])->json('DELETE', 'v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(403);
        $this->seeInDatabase('clients', ['client_id' => 'delete_me']);

        // Verify an admin key is able to delete a key
        $this->withLegacyApiKeyScopes(['admin'])->json('DELETE', 'v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(200);
        $this->dontSeeInDatabase('clients', ['client_id' => 'delete_me']);
    }
}
