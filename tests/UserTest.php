<?php

use Northstar\Models\User;

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

        $this->server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
        ];

        $this->serverRetrieveUser = [
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
        ];

        $this->userScope = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '123',
            'HTTP_X-DS-REST-API-Key' => '5464utyrs',
            'HTTP_Session' => 'S0FyZmlRNmVpMzVsSzJMNUFreEFWa3g0RHBMWlJRd0tiQmhSRUNxWXh6cz0=',
        ];

        // Mock AWS API class
        $this->awsMock = $this->mock('Northstar\Services\AWS');
    }

    /**
     * Test for retrieving a user with a non-admin key.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $response = $this->call('GET', 'v1/users/email/test@dosomething.org', [], [], [], $this->userScope);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
        $json = json_decode($content);

        // Check that public profile fields are visible...
        $this->assertObjectHasAttribute('id', $json->data);
        $this->assertObjectHasAttribute('email', $json->data);

        // ...and private profile fields are hidden.
        $this->assertObjectNotHasAttribute('last_name', $json->data);
    }

    /**
     * Test for retrieving a user with an admin key.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUser()
    {
        $response = $this->call('GET', 'v1/users/email/test@dosomething.org', [], [], [], $this->server);
        $content = $response->getContent();

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);
        $json = json_decode($content);

        // Check that public & private profile fields are visible
        $this->assertObjectHasAttribute('id', $json->data);
        $this->assertObjectHasAttribute('email', $json->data);
        $this->assertObjectHasAttribute('last_name', $json->data);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->call('GET', 'v1/users', [], [], [], $this->server);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent());
        $this->assertObjectHasAttribute('data', $data);
        $this->assertObjectHasAttribute('meta', $data);
        $this->assertObjectHasAttribute('pagination', $data->meta);
        $this->assertObjectHasAttribute('total', $data->meta->pagination);
        $this->assertObjectHasAttribute('count', $data->meta->pagination);
        $this->assertObjectHasAttribute('per_page', $data->meta->pagination);
        $this->assertObjectHasAttribute('current_page', $data->meta->pagination);
        $this->assertObjectHasAttribute('links', $data->meta->pagination);
    }

    /**
     * Test for retrieving a nonexistent User
     * GET /users/_id/FAKE
     *
     * @return void
     */
    public function testNonexistentUser()
    {
        $response = $this->call('GET', 'v1/users/_id/FAKE', [], [], [], $this->server);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Tests retrieving multiple users by their id
     * GET /users?filter[_id]=:id_1,...,:id_N
     * GET /users?filter[drupal_id]=:id_1,...,:id_N
     */
    public function testFilterUsersById()
    {
        // Retrieve multiple users by _id
        $response1 = $this->call(
            'GET',
            'v1/users?filter[_id]=5430e850dt8hbc541c37tt3d,5480c950bffebc651c8b456f,FAKE_ID',
            [], [], [], $this->server
        );
        $data1 = json_decode($response1->getContent());
        $this->assertCount(2, $data1->data);

        // Retrieve multiple users by drupal_id
        $response2 = $this->call(
            'GET',
            'v1/users?filter[drupal_id]=FAKE_ID,100001,100002,100003',
            [], [], [], $this->server
        );
        $data2 = json_decode($response2->getContent());
        $this->assertCount(3, $data2->data);

        // Test compound queries
        $response3 = $this->call(
            'GET',
            'v1/users?filter[drupal_id]=FAKE_ID,100001,100002,100003&filter[mobile]=5555550100',
            [], [], [], $this->server
        );
        $data3 = json_decode($response3->getContent());
        $this->assertCount(1, $data3->data);
    }

    /**
     * Tests searching users.
     * GET /users/?search[field]=term
     */
    public function testSearchUsers()
    {
        // Search should be limited to `admin` scoped keys.
        $response = $this->call(
            'GET',
            'v1/users?search[email]=test@dosomething.org',
            [], [], [], $this->userScope
        );
        $this->assertEquals(403, $response->getStatusCode());

        // Query by a "known" search term
        $response = $this->call(
            'GET',
            'v1/users?search[_id]=test@dosomething.org&search[email]=test@dosomething.org',
            [], [], [], $this->server
        );
        $data = json_decode($response->getContent());

        // There should be one match (a user with the provided email)
        $this->assertCount(1, $data->data);
    }

    /**
     * Tests retrieving a user
     * GET /users/{term}/{id}
     */
    public function testRetrieveUser()
    {
        // User info
        $user = User::find('5430e850dt8hbc541c37tt3d');

        // GET /users/_id/<user_id>
        $response = $this->call('GET', 'v1/users/_id/'.$user->_id, [], [], [], $this->serverRetrieveUser);
        $content = $response->getContent();
        $data = json_decode($content, true);

        // Assert response is 200 and has expected data
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('_id', $data['data']);

        // GET /users/mobile/<mobile>
        $response = $this->call('GET', 'v1/users/mobile/'.$user->mobile, [], [], [], $this->serverRetrieveUser);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('mobile', $data['data']);

        // GET /users/email/<email>
        $response = $this->call('GET', 'v1/users/email/'.$user->email, [], [], [], $this->serverRetrieveUser);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('email', $data['data']);

        // GET /users/drupal_id/<drupal_id>
        $response = $this->call('GET', 'v1/users/drupal_id/'.$user->drupal_id, [], [], [], $this->serverRetrieveUser);
        $content = $response->getContent();
        $data = json_decode($content, true);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($content);
        $this->assertArrayHasKey('drupal_id', $data['data']);
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
        $user = [
            'email' => 'new@dosomething.org',
            'mobile' => '5556667777',
            'password' => 'secret',
        ];

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
     * PUT /users/_id/:id
     *
     * @return void
     */
    public function testUpdateUser()
    {
        // Create a new user object
        $user = [
            'email' => 'newemail@dosomething.org',
            'parse_installation_ids' => 'parse-abc123',
        ];

        $response = $this->call('PUT', 'v1/users/_id/5480c950bffebc651c8b456f', [], [], [], $this->server, json_encode($user));
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

        $this->assertEquals('newemail@dosomething.org', $updatedUser['data']['email']);
        $this->assertEquals('parse-abc123', $updatedUser['data']['parse_installation_ids'][0]);
    }

    /**
     * Test for creating a user's profile image with a file
     * POST /users/:user_id/avatar
     *
     * @return void
     */
    public function testCreateUserAvatarWithFile()
    {
        $payload = [
            'photo' => 'example.jpeg',
        ];

        // Mock successful response from AWS API
        $this->awsMock->shouldReceive('storeImage')->once()->andReturn('http://bucket.s3.amazonaws.com/5480c950bffebc651c8b456f.jpg');

        $response = $this->call('POST', 'v1/users/5480c950bffebc651c8b456f/avatar', [], [], [], $this->server, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return avatar's url
        $this->assertNotEmpty($data['data']['photo']);
    }

    /**
     * Test for creating a user's profile image with a Base64 string
     * POST /users/:user_id/avatar
     *
     * @return void
     */
    public function testCreateUserAvatarWithBase64()
    {
        $payload = [
            'photo' => '123456789',
        ];

        // Mock successful response from AWS API
        $this->awsMock->shouldReceive('storeImage')->once()->andReturn('http://bucket.s3.amazonaws.com/5480c950bffebc651c8b456f.jpg');

        $response = $this->call('POST', 'v1/users/5480c950bffebc651c8b456f/avatar', [], [], [], $this->server, json_encode($payload));
        $content = $response->getContent();
        $data = json_decode($content, true);

        // The response should return a 200 status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $this->assertJson($content);

        // Response should return avatar's url
        $this->assertNotEmpty($data['data']['photo']);
    }

    /**
     * Test for deleting an existing user
     * DELETE /users
     *
     * @return void
     */
    public function testDelete()
    {
        $response = $this->call('DELETE', 'v1/users/5480c950bffebc651c8b4570', [], [], [], $this->server, []);

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
        $response = $this->call('DELETE', 'v1/users/DUMMY_ID', [], [], [], $this->server, []);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
