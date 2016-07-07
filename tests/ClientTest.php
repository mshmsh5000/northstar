<?php

use Northstar\Models\Client;

class ClientTest extends TestCase
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
        $this->asAdminUser()->get('v2/clients');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'client_id', 'client_secret', 'scope',
                ],
            ],
        ]);

        // Verify a "user" scoped key is not able to list keys
        $this->asNormalUser()->get('v2/clients');
        $this->assertResponseStatus(401);
    }

    /**
     * Test authentication & functionality of key creation endpoint.
     * @test
     */
    public function testStore()
    {
        $attributes = [
            'client_id' => 'dog', // hello this is doge key
            'scope' => ['admin'],
        ];

        // Verify a "user" scoped key is not able to create new keys
        $this->asNormalUser()->json('POST', 'v2/clients', $attributes);
        $this->assertResponseStatus(401);

        // Verify an admin key is able to create a new key
        $this->asAdminUser()->json('POST', 'v2/clients', $attributes);
        $this->assertResponseStatus(201);
        $this->seeJsonStructure([
            'data' => [
                'client_id', 'client_secret', 'scope',
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

        // Verify a non-admin user is not able to see whether a client exists or not
        $this->asNormalUser()->get('v2/clients/notarealkey');
        $this->assertResponseStatus(401);
        
        // Verify a non-admin user is not able to see keys details
        $this->asNormalUser()->get('v2/clients/'.$client->client_id);
        $this->assertResponseStatus(401);
        
        // Verify an admin key is able to view key details
        $this->asAdminUser()->get('v2/clients/'.$client->client_id);
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

        // Verify a non-admin user is not able to see whether a client exists or not
        $this->asNormalUser()->json('PUT', 'v2/clients/notarealkey');
        $this->assertResponseStatus(401);

        // Verify a non-admin user is not able to update keys
        $this->asNormalUser()->json('PUT', 'v2/clients/'.$client->client_id, $modifications);
        $this->assertResponseStatus(401);

        // Verify an admin is able to update a key
        $this->asAdminUser()->json('PUT', 'v2/clients/'.$client->client_id, $modifications);
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

        // Verify a non-admin user is not able to delete keys
        $this->asNormalUser()->json('DELETE', 'v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(401);
        $this->seeInDatabase('clients', ['client_id' => 'delete_me']);

        // Verify an admin is able to delete a key
        $this->asAdminUser()->json('DELETE', 'v1/keys/'.$client->client_secret);
        $this->assertResponseStatus(200);
        $this->dontSeeInDatabase('clients', ['client_id' => 'delete_me']);
    }
}
