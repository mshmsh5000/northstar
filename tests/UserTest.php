<?php

class UserTest extends TestCase
{

    /**
     * Migrate database and set up HTTP headers
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Artisan::call('migrate');
        $this->seed();

        $this->server = array(
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0='
        );
    }


    /**
     * Test for retrieving a user
     * GET /users
     *
     * @return void
     */
    public function testGetDataFromUser()
    {
        $response = $this->call('GET', 'v1/users/email/test@dosomething.org', [], [], [], $this->server);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
    }

    /**
     * Test for retrieving a nonexistant User
     * GET /users
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->call('GET', 'v1/users');
        $content = $response->getContent();

        // The response should return 404
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test for registering a new user
     * POST /users
     *
     * @return void
     */
    public function testRegisterUser()
    {
        // Create a new user object
        $user = array(
            'email' => 'new@dosomething.org',
            'mobile' => '5556667777',
            'password' => 'secret',
        );

        $response = $this->call('POST', 'v1/users', [], [], [], $this->server, json_encode($user));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 Okay status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return created at and id columns
        $this->assertArrayHasKey('created_at', $data['data']);
        $this->assertArrayHasKey('_id', $data['data']);
    }

    /**
     * Test for updating an existing user
     * PUT /users
     *
     * @return void
     */
    public function testUpdateUser()
    {
        // Create a new user object
        $user = array(
            'email' => 'newemail@dosomething.org',
            'parse_installation_ids' => 'parse-abc123',
        );

        $response = $this->call('PUT', 'v1/users/5480c950bffebc651c8b456f', [], [], [], $this->server, json_encode($user));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 202 Accepted status code
        $this->assertEquals(202, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return updated_at and unchanged user values should remain unchanged
        $this->assertArrayHasKey('updated_at', $data['data']);
        $this->assertEquals('5555550101', $data['data']['mobile']);

        // Verify user data got updated
        $getResponse = $this->call('GET', 'v1/users/_id/5480c950bffebc651c8b456f', [], [], [], $this->server);
        $getContent = $getResponse->getContent();
        $updatedUser = json_decode($getContent, true);

        $this->assertEquals('newemail@dosomething.org', $updatedUser['data'][0]['email']);
        $this->assertEquals('parse-abc123', $updatedUser['data'][0]['parse_installation_ids'][0]);
    }

    /**
     * Test for deleting an existing user
     * DELETE /users
     *
     * @return void
     */
    public function testDelete()
    {
        $response = $this->call('DELETE', 'v1/users/5480c950bffebc651c8b4570', [], [], [], $this->server, array());

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test for deleting a user that does not exist.
     * DELETE /users
     *
     * @return void
     */
    public function testDeleteNoResource()
    {
        $response = $this->call('DELETE', 'v1/users/DUMMY_ID', [], [], [], $this->server, array());

        $this->assertEquals(404, $response->getStatusCode());
    }
}
