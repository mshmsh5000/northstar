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
    public function testIndexVisibleToAdminRole()
    {
        // Make a admin to test acting as.
        $admin = factory(User::class)->create();
        $admin->role = 'admin';
        $admin->save();

        // Make some test users to see in the index.
        factory(User::class, 5)->create();

        $this->asUser($admin, ['role:admin'])->get('v1/users');
        $this->assertResponseStatus(200);
    }
}
