<?php

class ApiKeyTest extends TestCase
{
    /**
     * Test authentication & functionality of key index endpoint.
     * @test
     */
    public function testIndex()
    {
        // Verify an admin key is able to view all keys
        $this->withScopes(['admin'])->get('v1/keys');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'app_id', 'api_key', 'scope',
                ],
            ],
        ]);

        // Verify a "user" scoped key is not able to list keys
        $this->withScopes(['user'])->get('v1/keys');
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
        $this->withScopes(['user'])->json('POST', 'v1/keys', $attributes);
        $this->assertResponseStatus(403);

        // Verify an admin key is able to create a new key
        $this->withScopes(['admin'])->json('POST', 'v1/keys', $attributes);
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
        // Verify a "user" scoped key is not able to see keys details
        $this->withScopes(['user'])->get('v1/keys/abc4324');
        $this->assertResponseStatus(403);

        // Verify a "user" scoped key is not able to see whether a key exists or not
        $this->withScopes(['user'])->get('v1/keys/notarealkey');
        $this->assertResponseStatus(403);

        // Verify an admin key is able to view key details
        $this->withScopes(['admin'])->get('v1/keys/abc4324');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'app_id', 'api_key', 'scope'
            ],
        ]);
    }

    /**
     * Test authentication & functionality of key creation endpoint.
     * @test
     */
    public function testUpdate()
    {
        $modifications = [
            'scope' => [
                'admin',
                'user',
            ],
        ];

        // Verify a "user" scoped key is not able to update keys
        $this->withScopes(['user'])->json('PUT', 'v1/keys/5464utyrs', $modifications);
        $this->assertResponseStatus(403);

        // Verify an admin key is able to update a key
        $this->withScopes(['admin'])->json('PUT', 'v1/keys/5464utyrs', $modifications);
        $this->assertResponseStatus(200);
        $this->seeInDatabase('api_keys', [
            'api_key' => '5464utyrs',
            'scope' => ['admin', 'user'],
        ]);
    }

    /**
     * Test authentication & functionality of key deletion endpoint.
     * @test
     */
    public function testDestroy()
    {
        // Verify a "user" scoped key is not able to delete keys
        $this->withScopes(['user'])->json('DELETE', 'v1/keys/5464utyrs');
        $this->assertResponseStatus(403);

        // Verify an admin key is able to delete a key
        $this->withScopes(['admin'])->json('DELETE', 'v1/keys/5464utyrs');
        $this->assertResponseStatus(200);
        $this->dontSeeInDatabase('api_keys', ['api_key' => '5464utyrs']);
    }
}
