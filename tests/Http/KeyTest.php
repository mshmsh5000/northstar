<?php

use Northstar\Models\User;

class KeyTest extends BrowserKitTestCase
{
    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testKeyNotVisibleToUserRole()
    {
        $this->get('v2/key');
        $this->assertResponseStatus(401);

        $this->asNormalUser()->get('v2/key');
        $this->assertResponseStatus(401);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testKeyNotVisibleToStaffRole()
    {
        // Make a staff user & some test users.
        $staff = factory(User::class, 'staff')->create();

        $this->asUser($staff, ['role:staff'])->get('v2/key');
        $this->assertResponseStatus(401);
    }

    /**
     * Test retrieving multiple users.
     * GET /users
     *
     * @return void
     */
    public function testKeyVisibleToAdminRole()
    {
        $this->asAdminUser()->get('v2/key');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'algorithm',
            'issuer',
            'public_key',
        ]);
    }
}
