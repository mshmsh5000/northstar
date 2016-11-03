<?php

use Northstar\Models\User;

class WebUserTest extends TestCase
{
    /**
     * Test that an authenticated, unauthorized user cannot see the
     * /users/:id page and is redirected to view their profile on
     * the homepage.
     */
    public function testProfileShowRedirect()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/'.$user->id)
             ->seePageIs('/');
    }

    /**
     * Test that users can click to edit their profile from the homepage.
     */
    public function testSeeProfileEditForm()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('/')
             ->click('Edit Profile')
             ->seePageIs('users/'.$user->id.'/edit');
    }

    /**
     * Test that users can cancel out of editing their profile and head
     * back to the homepage.
     */
    public function testCancelProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/'.$user->id.'/edit')
             ->click('Cancel')
             ->seePageIs('/');
    }

    /**
     * Test that users can edit their profile.
     */
    public function testProfileEdit()
    {
        $user = $this->makeAuthWebUser();

        $this->visit('users/'.$user->id.'/edit')
             ->type('Jean-Paul', 'first_name')
             ->type('Beaubier', 'last_name')
             ->press('Save')
             ->seePageIs('/');

        $updatedUser = User::find($user->id);

        $this->assertEquals('Jean-Paul', $updatedUser->first_name);
        $this->assertEquals('Beaubier', $updatedUser->last_name);
    }
}
