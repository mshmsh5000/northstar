<?php

use Carbon\Carbon;
use Northstar\Models\User;

class UserTest extends TestCase
{
    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexNotVisibleToUserRole()
    {
        // Make a normal user to test acting as.
        $user = factory(User::class)->create();

        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->asUser($user, ['role:admin'])->get('v1/users');
        $this->assertResponseStatus(401);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class, 'staff')->create();
        factory(User::class, 5)->create();

        $this->asUser($staff, ['role:staff'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testIndexVisibleToAdminRole()
    {
        // Make a admin & some test users.
        $admin = factory(User::class, 'admin')->create();
        factory(User::class, 5)->create();

        $this->asUser($admin, ['role:admin'])->get('v1/users');
        $this->assertResponseStatus(200);
    }

    /**
     * Test that retrieving a user as a non-admin returns limited profile.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetPublicDataFromUser()
    {
        $user = factory(User::class)->create();
        $viewer = factory(User::class)->create();

        // Test that we can view public profile as another user.
        $this->asUser($viewer, ['user', 'user:admin'])->get('v1/users/_id/'.$user->id);
        $this->assertResponseStatus(200);

        // And test that private profile fields are hidden for the other user.
        $data = $this->decodeResponseJson()['data'];
        $this->assertArrayHasKey('first_name', $data);
        $this->assertArrayNotHasKey('last_name', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('mobile', $data);
        $this->assertArrayNotHasKey('facebook_id', $data);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUserAsStaff()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'staff')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v1/users/id/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that retrieving a user as an admin returns full profile.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGetAllDataFromUserAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'user:admin'])->get('v1/users/id/'.$user->id);
        $this->assertResponseStatus(200);

        // Check that public & private profile fields are visible
        $this->seeJsonStructure([
            'data' => [
                'id', 'email', 'first_name', 'last_name', 'facebook_id',
            ],
        ]);
    }

    /**
     * Test that a staffer can update a user's profile.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testUpdateProfileAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
        ]);

        $this->assertResponseStatus(200);

        // The user should remain unchanged.
        $user->fresh();
        $this->assertNotEquals('Alexander', $user->first_name);
        $this->assertNotEquals('Hamilton', $user->last_name);
    }

    /**
     * Test that a staffer cannot change a user's role.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testGrantRoleAsStaff()
    {
        $user = factory(User::class)->create();
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['user', 'role:staff'])->json('PUT', 'v1/users/id/'.$user->id, [
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Test that an admin can create a new user.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testCreateUser()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'email' => $this->faker->email,
            'source' => 'historical',
            'source_detail' => 'american-revolution',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'data' => [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'source' => 'historical',
                'source_detail' => 'american-revolution',
            ],
        ]);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * GET /users/:term/:id
     *
     * @return void
     */
    public function testUpdateProfileAsAdmin()
    {
        $user = factory(User::class)->create();
        $admin = factory(User::class, 'admin')->create();

        $this->asUser($admin, ['user', 'role:admin'])->json('PUT', 'v1/users/id/'.$user->id, [
            'first_name' => 'Hercules',
            'last_name' => 'Mulligan',
            'role' => 'admin',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'first_name' => 'Hercules',
                'last_name' => 'Mulligan',
                'role' => 'admin',
            ],
        ]);
    }

    /**
     * Test that creating a user results in saving normalized data.
     * POST /users
     *
     * @return void
     */
    public function testFieldsAreNormalized()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'first_name' => 'Batman',
            'email' => 'BatMan@example.com',
            'mobile' => '1 (222) 333-5555',
        ]);

        $this->assertResponseStatus(201);
        $this->seeInDatabase('users', [
            'first_name' => 'Batman',
            'email' => 'batman@example.com',
            'mobile' => '2223335555',
        ]);
    }

    /**
     * Test that an admin can update a user's profile, including their role.
     * POST /v1/users/
     *
     * @return void
     */
    public function testUTF8Fields()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'woot-woot@example.com',
            'last_name' => '└(^o^)┘',
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonSubset([
            'data' => [
                'last_name' => '└(^o^)┘',
                'last_initial' => '└',
            ],
        ]);
    }

    /**
     * Test that users get created_at & updated_at fields.
     * POST /v1/users/
     *
     * @return void
     */
    public function testSetCreatedAtField()
    {
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => 'alejandro@example.com',
        ]);

        $this->assertResponseStatus(201);

        // Let's see what 'created_at' is returned in the response
        $response = $this->decodeResponseJson();
        $date = new Carbon($response['data']['created_at']);

        // It should be today!
        $this->assertTrue($date->isSameDay(Carbon::now()));

        // And it should be stored as a ISODate in the actual database.
        $this->seeInDatabase('users', [
            'email' => 'alejandro@example.com',
            'created_at' => new MongoDB\BSON\UTCDateTime($date->getTimestamp() * 1000),
        ]);
    }

    /**
     * Test that we can only upsert created_at to be earlier.
     * POST /v1/users/
     *
     * @return void
     */
    public function testUpsertCreatedAtField()
    {
        $user = factory(User::class)->create([
            'first_name' => 'Daisy',
            'last_name' => 'Johnson',
            'source' => 'television',
            'source_detail' => 'agents-of-shield',
        ]);

        // We finally read Secret War #2, and want to update her 'created_at' date to match her first appearance.
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => $user->email,
            'first_name' => 'Daisy',
            'created_at' => '1088640000', // first comic book appearance!
            'source' => 'comic',
            'source_detail' => 'secret-war/2',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => $user->email,
                'first_name' => 'Daisy',
                'source' => 'comic',
                'source_detail' => 'secret-war/2',
                'created_at' => '2004-07-01T00:00:00+0000',
            ],
        ]);
    }

    /**
     * Test that we can only upsert created_at to be earlier.
     * POST /v1/users/
     *
     * @return void
     */
    public function testUpsertSourceWithoutDetail()
    {
        $user = factory(User::class)->create([
            'source' => 'factory',
            'source_detail' => 'user-factory',
        ]);

        // We finally read Secret War #2, and want to update her 'created_at' date to match her first appearance.
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => $user->email,
            'created_at' => '1088640000',
            'source' => 'phpunit',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'email' => $user->email,
                'source' => 'phpunit',
                'source_detail' => null,
                'created_at' => '2004-07-01T00:00:00+0000',
            ],
        ]);
    }

    /**
     * Test that we can only upsert created_at to be earlier.
     * POST /v1/users/
     *
     * @return void
     */
    public function testUpsertNewerCreatedAtField()
    {
        $user = factory(User::class)->create(['first_name' => 'Nathaniel', 'last_name' => 'Richards']);
        $created_at = $user->created_at;

        // We don't allow time-travelling (setting your created_at date to be later).
        $this->asAdminUser()->json('POST', 'v1/users', [
            'email' => $user->email,
            'created_at' => '2540246400', // the fuuuuuuuuture!
        ]);

        $this->assertResponseStatus(200);

        // The request should have completed, but the `created_at` should not have changed
        $this->assertEquals((string) $created_at, (string) $user->fresh()->created_at);
    }
}
