<?php

use Northstar\Models\User;

class LegacyUserTest extends TestCase
{
    /**
     * Test for retrieving a user by their ID.
     * GET /users/id/:id
     *
     * @return void
     */
    public function testGetUserById()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->get('v1/users/id/'.$user->id);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->id,
            ],
        ]);
    }

    /**
     * Test for retrieving a user by their Mongo _id, for backwards compatibility.
     * GET /users/_id/:id
     *
     * @return void
     */
    public function testGetUserByMongoId()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->get('v1/users/_id/'.$user->id);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->id,
            ],
        ]);
    }

    /**
     * Test for retrieving a user by their email.
     * GET /users/email/:email
     *
     * @return void
     */
    public function testGetUserByEmail()
    {
        $user = User::create([
            'email' => 'jbeaubier@xavier.edu',
            'first_name' => 'Jean-Paul',
        ]);

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get('v1/users/email/JBeaubier@Xavier.edu');
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->id,
            ],
        ]);
    }

    /**
     * Test for retrieving a user by their mobile number.
     * GET /users/email/:email
     *
     * @return void
     */
    public function testGetUserByMobile()
    {
        $user = User::create([
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get('v1/users/mobile/'.$user->mobile);
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->id,
            ],
        ]);
    }

    /**
     * Test we can't retrieve a user by a non-indexed field.
     * GET /users/email/:email
     *
     * @return void
     */
    public function testCantGetUserByNonIndexedField()
    {
        User::create([
            'mobile' => $this->faker->phoneNumber,
            'first_name' => 'Bobby',
        ]);

        // Test that we return 404 when retrieving by a non-indexed field.
        $this->withLegacyApiKeyScopes(['user'])->get('v1/users/first_name/Bobby');
        $this->assertResponseStatus(404);
    }

    /**
     * Tests retrieving a user by their Drupal ID.
     * GET /users/drupal_id/{id}
     */
    public function testRetrieveUser()
    {
        $user = factory(User::class)->create(['drupal_id' => '100010']);

        // GET /users/drupal_id/<drupal_id>
        $this->get('v1/users/drupal_id/100010');
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->id,
                'drupal_id' => $user->drupal_id,
            ],
        ]);
    }

    /**
     * Test for retrieving a user with a non-admin key.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ]);

        // Test that we can view public profile of the user.
        $this->withLegacyApiKeyScopes(['user'])->get('v1/users/_id/'.$user->id);
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'first_name',
            ],
        ]);

        // And test that private profile fields are hidden for 'user' scope.
        $data = $this->decodeResponseJson()['data'];
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('last_name', $data);
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

        $this->withLegacyApiKeyScopes(['user', 'admin'])->get('v1/users/_id/'.$user->id);
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
        $this->assertResponseStatus(403);

        $this->withLegacyApiKeyScopes(['admin'])->get('v1/users');
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

        $this->withLegacyApiKeyScopes(['admin'])->get('v1/users?limit=200'); // set a "per page" above the allowed max
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
     * GET /users?filter[id]=:id_1,...,:id_N
     * GET /users?filter[drupal_id]=:id_1,...,:id_N
     */
    public function testFilterUsersById()
    {
        $user1 = factory(User::class)->create(['email' => $this->faker->unique()->email, 'drupal_id' => '123411']);
        $user2 = factory(User::class)->create(['email' => $this->faker->unique()->email, 'drupal_id' => '123412']);
        $user3 = factory(User::class)->create(['mobile' => $this->faker->unique()->phoneNumber, 'drupal_id' => '123413']);

        // Retrieve multiple users by _id
        $this->withLegacyApiKeyScopes(['admin'])->get('v1/users?filter[id]='.$user1->id.','.$user2->id.',FAKE_ID');
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
        $this->withLegacyApiKeyScopes(['admin'])->get('v1/users?filter[drupal_id]=FAKE_ID,'.$user1->drupal_id.','.$user2->drupal_id.','.$user3->drupal_id);
        $this->assertCount(3, $this->decodeResponseJson()['data']);

        // Test compound queries
        $this->withLegacyApiKeyScopes(['admin'])->get('v1/users?filter[drupal_id]=FAKE_ID,'.$user1->drupal_id.','.$user2->drupal_id.','.$user3->drupal_id.'&filter[_id]='.$user1->id);
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
        $this->withLegacyApiKeyScopes(['admin'])
            ->get('v1/users?search[_id]=search-result@dosomething.org&search[email]=search-result@dosomething.org');
        $this->assertResponseStatus(200);

        // There should be one match (a user with the provided email)
        $this->assertCount(1, $this->decodeResponseJson()['data']);
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

        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', $payload);
        $this->assertResponseStatus(201);
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
            $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
                'email' => $this->faker->unique()->email,
                'mobile' => '', // this should not save a `mobile` field on these users
                'source' => 'phpunit',
            ]);

            $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
                'email' => '  ', // this should not save a `email` field on these users
                'mobile' => $this->faker->unique()->phoneNumber,
                'source' => 'phpunit',
            ]);
        }

        $this->get('v1/users');
        $this->assertCount(10, $this->decodeResponseJson()['data']);
    }

    /**
     * Test that you set an indexed field to an empty string. This would cause
     * unique constraint violations if multiple users had an empty string set
     * for a unique indexed field.
     * PUT /users/:id
     *
     * @return void
     */
    public function testCantMakeIndexEmptyString()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'mobile' => '', // this should remove the `mobile` field from the document
        ]);

        $this->seeInDatabase('users', ['_id' => $user->id]);

        $document = $this->getMongoDocument('users', $user->id);
        $this->assertArrayNotHasKey('mobile', $document);
    }

    /**
     * Test that you can't remove the only index (email or mobile) from a field.
     * PUT /users/:id
     *
     * @return void
     */
    public function testCantRemoveOnlyIndex()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'email' => '',
        ]);

        $this->assertResponseStatus(422);
    }

    /**
     * Test that you can't remove *both* the email and mobile fields from a user.
     * PUT /users/:id
     *
     * @return void
     */
    public function testCantRemoveBothEmailAndMobile()
    {
        $user = User::create([
            'email' => $this->faker->email,
            'mobile' => $this->faker->phoneNumber,
            'first_name' => $this->faker->firstName,
        ]);

        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'email' => '',
            'mobile' => '',
        ]);
        $this->assertResponseStatus(422);
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

        // This should cause a validation error.
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', $payload);
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

        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'EXISTING-USER@dosomething.org',
            'source' => 'phpunit',
        ]);

        $this->assertResponseStatus(200);
        $this->assertSame($this->decodeResponseJson()['data']['id'], $user->_id);
    }

    /**
     * Test that we can upsert based on a Drupal ID.
     * POST /users
     *
     * @return void
     */
    public function testCanUpsertByDrupalId()
    {
        $user = factory(User::class)->create([
            'email' => 'existing-person@example.com',
            'drupal_id' => '123123',
        ]);

        // Try to make a conflict up by upserting something that would match 2 accounts.
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
            'drupal_id' => '123123',
            'first_name' => 'Bob',
        ]);

        $this->assertResponseStatus(200);

        $user = $user->fresh();
        $this->assertEquals('Bob', $user->first_name);
    }

    /**
     * Test that we can't create a duplicate user.
     * POST /users
     *
     * @return void
     */
    public function testCantCreateDuplicateDrupalUser()
    {
        factory(User::class)->create([
            'email' => 'existing-person@example.com',
            'drupal_id' => '123123',
        ]);

        factory(User::class)->create([
            'email' => 'other-existing-user@example.com',
        ]);

        // Try to make a conflict up by upserting something that would match 2 accounts.
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'other-existing-user@example.com',
            'drupal_id' => '123123',
        ]);

        $this->assertResponseStatus(422);
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

        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
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
        factory(User::class)->create([
            'email' => 'upsert-me@dosomething.org',
            'mobile' => null,  // <-- overriding factory so we can add it via upsert
            'source' => 'database',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '5556667777',
            'password' => 'secret',
            'first_name' => 'Puppet',
            'source' => 'phpunit',
            'role' => 'admin',
        ]);

        // The response should return JSON with a 200 Okay status code
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => 'upsert-me@dosomething.org',

                // Check for the new fields we "upserted":
                'first_name' => 'Puppet',
                'mobile' => '5556667777',

                // Ensure the `source` field is immutable (since we did not provide an earlier creation date):
                'source' => 'database',

                // The role should *not* be changed by upsert (since that'd make it easily to accidentally grant!)
                'role' => 'user',
            ],
        ]);
    }

    /**
     * Test for opting out of upsert functionality via a query string.
     * POST /users?upsert=false
     *
     * @return void
     */
    public function testOptOutOfUpsertingUser()
    {
        $user = User::create([
            'email' => 'do-not-upsert-me@dosomething.org',
            'source' => 'database',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users?upsert=false', [
            'email' => $user->email,
            'first_name' => 'Puppet',
        ]);

        // The response should return 422 Unprocessable Entity with the existing item.
        $this->assertResponseStatus(422);
        $this->assertEquals($user->id, $this->decodeResponseJson()['error']['context']['id']);
    }

    /**
     * Test that we can still create a new user when we've opted out of upserting.
     * POST /users?upsert=false
     *
     * @return void
     */
    public function testCreateUserWhileOptingOutOfUpsert()
    {
        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users?upsert=false', [
            'email' => $this->faker->email,
            'first_name' => 'Puppet',
        ]);

        // This should still be allowed, since the account doesn't exist.
        $this->assertResponseStatus(201);
    }

    /**
     * Test that "upserting" an existing user can't change an existing
     * user's account if *all* given credentials don't match.
     * POST /users
     *
     * @return void
     */
    public function testCantUpsertUserWithoutAllMatchingCredentials()
    {
        $user = User::create([
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '5556667777',
        ]);

        // Post a "new" user object to merge into existing record
        $this->withLegacyApiKeyScopes(['admin'])->json('POST', 'v1/users', [
            'email' => 'upsert-me+2@dosomething.org',
            'mobile' => '5556667777',
            'first_name' => 'Puppet',
        ]);

        // The existing record should be unchanged.
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'email' => 'upsert-me@dosomething.org',
            'mobile' => '5556667777',
        ]);

        // The response should indicate a validation conflict!
        $this->assertResponseStatus(422);
        $this->seeJsonSubset([
            'error' => [
                'code' => 422,
                'message' => 'Failed validation.',
                'fields' => [
                    'email' => ['Cannot upsert an existing index.'],
                ],
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
        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'email' => 'NewEmail@dosomething.org',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => 'newemail@dosomething.org',
                'mobile' => $user->mobile, // unchanged user values should remain unchanged
            ],
        ]);

        // Verify user data got updated
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'mobile' => $user->mobile,
            'email' => 'newemail@dosomething.org',
        ]);
    }

    /**
     * Test for updating an existing user's index.
     * PUT /users/_id/:id
     *
     * @return void
     */
    public function testUpdateUserIndex()
    {
        $user = User::create(['email' => 'email@dosomething.org']);

        // Update an existing user
        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'email' => 'new-email@dosomething.org',
        ]);

        $this->assertResponseStatus(200);

        // Verify user data got updated
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'email' => 'new-email@dosomething.org',
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

        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'mobile' => '(555) 555-0101', // the existing user account
            'first_name' => 'Gial',
            'last_name' => 'Ackbar',
        ]);

        $this->assertResponseStatus(422);
    }

    /**
     * Test that we can't update a user's profile to have duplicate
     * identifiers with someone else.
     * PUT /users/_id/:id
     */
    public function testUpdateWithDrupalIDConflict()
    {
        factory(User::class)->create(['drupal_id' => '123456']);
        $user = factory(User::class)->create(['email' => 'admiral.ackbar@example.com']);

        $this->withLegacyApiKeyScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->id, [
            'drupal_id' => '123456', // the existing user account
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

        $this->asUserUsingLegacyAuth($user)->withLegacyApiKeyScopes(['user'])->json('POST', 'v1/users/'.$user->id.'/avatar', [
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

        $this->asUserUsingLegacyAuth($user)->withLegacyApiKeyScopes(['user'])->json('POST', 'v1/users/'.$user->id.'/avatar', [
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

        $this->withLegacyApiKeyScopes(['admin'])->delete('v1/users/'.$user->id);
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
        $this->withLegacyApiKeyScopes(['admin'])->delete('v1/users/DUMMY_ID');
        $this->assertResponseStatus(404);
    }
}
