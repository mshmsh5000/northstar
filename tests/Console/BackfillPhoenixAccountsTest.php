<?php

use Northstar\Models\User;

class BackfillPhoenixAccountsTest extends BrowserKitTestCase
{
    /**
     * Test that it does the thing its supposed to.
     * GET /users
     *
     * @return void
     */
    public function testThatItDoesTheThings()
    {
        // Make a ton of test accounts (without Drupal IDs).
        factory(User::class, 10)->create();
        $this->assertEquals(10, User::whereNull('drupal_id')->count());

        // Run the magic command on our messy data.
        $this->artisan('northstar:backfill_phoenix');

        // After running the command, there should be no more empty Drupal IDs.
        $this->assertEquals(0, User::whereNull('drupal_id')->count());
    }
}
