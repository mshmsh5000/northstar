<?php

use Northstar\Models\User;

class UserTest extends TestCase
{
    /**
     * Test for retrieving a user with a non-admin key.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $user = User::create([
            'email' => $this->faker->unique()->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        // Test that we can view public profile of the user.
        $this->withScopes(['user'])->get('v1/users/_id/'.$user->id);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name',
            ],
        ]);

        // And test that private profile fields are hidden for 'user' scope.
        $this->assertArrayNotHasKey('last_name', $this->decodeResponseJson()['data']);
    }

    /**
     * Test for retrieving a user with an admin key.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUser()
    {
        $user = User::create([
            'email' => $this->faker->unique()->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        $this->withScopes(['user', 'admin'])->get('v1/users/_id/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name',
            ],
        ]);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndex()
    {
        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->get('v1/users');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
            'meta' => [
                'pagination' => [
                   'total', 'count', 'per_page', 'current_page', 'links',
                ],
            ],
        ]);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexPagination()
    {
        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->get('v1/users?limit=200'); // set a "per page" above the allowed max
        $this->assertResponseStatus(200);
        $this->assertSame(100, $this->decodeResponseJson()['meta']['pagination']['per_page']);

        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
            'meta' => [
                'pagination' => [
                    'total', 'count', 'per_page', 'current_page', 'links',
                ],
            ],
        ]);
    }

    /**
     * Test for retrieving a nonexistent User
     * GET /users/_id/FAKE
     *
     * @return void
     */
    public function testNonexistentUser()
    {
        $this->get('v1/users/_id/FAKE');
        $this->assertResponseStatus(404);
    }

    /**
     * Tests retrieving multiple users by their id
     * GET /users?filter[_id]=:id_1,...,:id_N
     * GET /users?filter[drupal_id]=:id_1,...,:id_N
     */
    public function testFilterUsersById()
    {
        $user1 = User::create(['email' => $this->faker->unique()->email, 'drupal_id' => '123411']);
        $user2 = User::create(['email' => $this->faker->unique()->email, 'drupal_id' => '123412']);
        $user3 = User::create(['mobile' => $this->faker->unique()->phoneNumber, 'drupal_id' => '123413']);

        // Retrieve multiple users by _id
        $this->get('v1/users?filter[_id]='.$user1->id.','.$user2->id.',FAKE_ID');
        $this->assertCount(2, $this->decodeResponseJson()['data']);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                ],
            ],
            'meta' => [
                'pagination',
            ],
        ]);

        // Retrieve multiple users by drupal_id
        $this->get('v1/users?filter[drupal_id]=FAKE_ID,'.$user1->drupal_id.','.$user2->drupal_id.','.$user3->drupal_id);
        $this->assertCount(3, $this->decodeResponseJson()['data']);

        // Test compound queries
        $this->get('v1/users?filter[drupal_id]=FAKE_ID,'.$user1->drupal_id.','.$user2->drupal_id.','.$user3->drupal_id.'&filter[_id]='.$user1->id);
        $this->assertCount(1, $this->decodeResponseJson()['data']);
    }

    /**
     * Tests searching users.
     * GET /users/?search[field]=term
     */
    public function testSearchUsers()
    {
        // Make a test user to search for.
        User::create([
            'email' => 'search-result@dosomething.org',
        ]);

        // Search should be limited to `admin` scoped keys.
        $this->get('v1/users?search[email]=search-result@dosomething.org');
        $this->assertResponseStatus(403);

        // Query by a "known" search term
        $this->withScopes(['admin'])
            ->get('v1/users?search[_id]=search-result@dosomething.org&search[email]=search-result@dosomething.org');
        $this->assertResponseStatus(200);

        // There should be one match (a user with the provided email)
        $this->assertCount(1, $this->decodeResponseJson()['data']);
    }

    /**
     * Tests retrieving a user
     * GET /users/{term}/{id}
     */
    public function testRetrieveUser()
    {
        // User info
        $user = User::create([
            'email' => 'sterling.archer@example.com',
            'mobile' => '5551231245',
            'drupal_id' => '4567890',
        ]);

        // GET /users/_id/<user_id>
        $this->get('v1/users/_id/'.$user->_id);

        // Assert response is 200 and has expected data
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'email' => $user->email,
            ],
        ]);

        // GET /users/mobile/<mobile>
        $this->get('v1/users/mobile/'.$user->mobile);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'mobile' => $user->mobile,
            ],
        ]);

        // GET /users/email/<email>
        $this->get('v1/users/email/'.$user->email);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'email' => $user->email,
            ],
        ]);

        // GET /users/drupal_id/<drupal_id>
        $this->get('v1/users/drupal_id/'.$user->drupal_id);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'drupal_id' => $user->drupal_id,
            ],
        ]);
    }

    /**
     * Test for creating a new user.
     * POST /users
     *
     * @return void
     */
    public function testCreateUser()
    {
        // Create a new user object
        $payload = [
            'email' => 'new@dosomething.org',
            'source' => 'phpunit',
        ];

        $this->withScopes(['admin'])->json('POST', 'v1/users', $payload);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'source', 'created_at',
            ],
        ]);
    }

    /**
     * Test that creating multiple users won't trigger unique
     * database constraint errors.
     * POST /users
     *
     * @return void
     */
    public function testCreateMultipleUsers()
    {
        // Create some new users
        for ($i = 0; $i < 5; $i++) {
            $this->withScopes(['admin'])->json('POST', 'v1/users', [
                'email' => $this->faker->unique()->email,
                'mobile' => '', // this should not save a `mobile` field on these users
                'source' => 'phpunit',
            ]);

            $this->withScopes(['admin'])->json('POST', 'v1/users', [
                'email' => '  ', // this should not save a `email` field on these users
                'mobile' => $this->faker->unique()->phoneNumber,
                'source' => 'phpunit',
            ]);
        }

        $this->get('v1/users');
        $this->assertCount(10, $this->decodeResponseJson()['data']);
    }

    /**
     * Test that we can't create a duplicate user.
     * POST /users
     *
     * @return void
     */
    public function testCreateDuplicateUser()
    {
        User::create(['mobile' => '1235557878']);
        User::create(['email' => 'existing-person@example.com']);

        // Create a new user object
        $payload = [
            'email' => 'Existing-Person@example.com',
            'mobile' => '(123) 555-7878',
            'source' => 'phpunit',
        ];

        // This should upsert the existing user.
        $this->withScopes(['admin'])->json('POST', 'v1/users', $payload);
        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can't create a duplicate user by saving a user
     * with a different capitalization in their email.
     * POST /users
     */
    public function testCantCreateDuplicateUserByIndexCapitalization()
    {
        $user = User::create([
            'email' => 'existing-user@dosomething.org',
        ]);

        $this->withScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'EXISTING-USER@dosomething.org',
            'source' => 'phpunit',
        ]);

        $this->assertResponseStatus(200);
        $this->assertSame($this->decodeResponseJson()['data']['id'], $user->_id);
    }

    /**
     * Test that we can't create a duplicate user by "upserting" an existing
     * user and adding a new index in that operation.
     * POST /users
     */
    public function testCanUpsertWithAnAdditionalIndex()
    {
        $user = User::create([
            'mobile' => '2035551238',
        ]);

        $this->withScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'lalalala@dosomething.org',
            'mobile' => '2035551238',
            'source' => 'phpunit',
        ]);

        $this->assertResponseStatus(200);
        $this->assertSame($this->decodeResponseJson()['data']['id'], $user->_id);
    }

    /**
     * Test for "upserting" an existing user.
     * POST /users
     *
     * @return void
     */
    public function testUpsertUser()
    {
        User::create([
            'email' => 'upsert-me@dosomething.org',
            'source' => 'database',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '5556667777',
            'password' => 'secret',
            'first_name' => 'Puppet',
            'source' => 'phpunit',
        ]);

        // The response should return JSON with a 200 Okay status code
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => 'upsert-me@dosomething.org',
                // Check for the new fields we "upserted":
                'first_name' => 'Puppet',
                'mobile' => '5556667777',
                // Ensure the `source` field is immutable (since we tried to update to 'phpunit'):
                'source' => 'database',
            ],
        ]);
    }

    /**
     * Test for updating an existing user
     * PUT /users/_id/:id
     *
     * @return void
     */
    public function testUpdateUser()
    {
        $user = User::create(['mobile' => $this->faker->unique()->phoneNumber]);

        // Update an existing user
        $this->withScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'email' => 'NewEmail@dosomething.org',
            'parse_installation_ids' => 'parse-abc123',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => 'newemail@dosomething.org',
                'parse_installation_ids' => ['parse-abc123'],
                'mobile' => $user->mobile, // unchanged user values should remain unchanged
            ],
        ]);

        // Verify user data got updated
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'mobile' => $user->mobile,
            'email' => 'newemail@dosomething.org',
            'parse_installation_ids' => ['parse-abc123'],
        ]);
    }

    /**
     * Test that we can't update a user's profile to have duplicate
     * identifiers with someone else.
     * PUT /users/_id/:id
     */
    public function testUpdateWithConflict()
    {
        User::create(['mobile' => '5555550101']);

        $user = User::create(['email' => 'admiral.ackbar@example.com']);

        $this->withScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'mobile' => '(555) 555-0101', // the existing user account
            'first_name' => 'Gial',
            'last_name' => 'Ackbar',
        ]);

        $this->assertResponseStatus(422);
    }

    /**
     * Test for creating a user's profile image with a file
     * POST /users/:user_id/avatar
     *
     * @return void
     */
    public function testCreateUserAvatarWithFile()
    {
        $user = User::create();

        // Mock successful response from AWS API
        $this->mock('Northstar\Services\AWS')->shouldReceive('storeImage')->once()->andReturn('http://bucket.s3.amazonaws.com/'.$user->id.'-1234567.jpg');

        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/users/'.$user->id.'/avatar', [
            'photo' => 'example.jpeg',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'photo' => 'http://bucket.s3.amazonaws.com/'.$user->id.'-1234567.jpg',
            ],
        ]);
    }

    /**
     * Test for creating a user's profile image with a Base64 string
     * POST /users/:user_id/avatar
     *
     * @return void
     */
    public function testCreateUserAvatarWithBase64()
    {
        $user = User::create();

        // Mock successful response from AWS API
        $this->mock('Northstar\Services\AWS')->shouldReceive('storeImage')->once()->andReturn('http://bucket.s3.amazonaws.com/'.$user->id.'-123415.jpg');

        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/users/'.$user->id.'/avatar', [
            'photo' => '123456789',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'photo' => 'http://bucket.s3.amazonaws.com/'.$user->id.'-123415.jpg',
            ],
        ]);
    }

    /**
     * Test for deleting an existing user
     * DELETE /users
     *
     * @return void
     */
    public function testDelete()
    {
        $user = User::create(['email' => 'delete-me@example.com']);

        // Only 'admin' scoped keys should be able to delete users.
        $this->delete('v1/users/'.$user->id);
        $this->assertResponseStatus(403);

        $this->withScopes(['admin'])->delete('v1/users/'.$user->id);
        $this->assertResponseStatus(200);
    }

    /**
     * Test for deleting a user that does not exist.
     * DELETE /users
     *
     * @return void
     */
    public function testDeleteNoResource()
    {
        $this->withScopes(['admin'])->delete('v1/users/DUMMY_ID');
        $this->assertResponseStatus(404);
    }
}
