<?php

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
                'id', 'email', 'first_name', 'last_name',
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
                'id', 'email', 'first_name', 'last_name',
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
}
