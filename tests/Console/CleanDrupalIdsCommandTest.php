<?php

use Carbon\Carbon;
use Northstar\Models\User;

class CleanDrupalIdsCommandTest extends BrowserKitTestCase
{
    /**
     * Test that it does the thing its supposed to.
     * GET /users
     *
     * @return void
     */
    public function testThatItDeletesTheDupes()
    {
        // Make two users that we'll create a bunch of duplicates for.
        $tony = User::forceCreate(['first_name' => 'Tony', 'last_name' => 'Stark', 'drupal_id' => '12345']);
        $tony->setCreatedAt(new Carbon('March 10 1963'))->save();

        $steve = User::forceCreate(['first_name' => 'Steve', 'last_name' => 'Rogers', 'drupal_id' => '12346']);
        $steve->setCreatedAt(new Carbon('July 4 1920'))->save();

        // Make a user with a Drupal ID, but no duplicates.
        $kamala = User::forceCreate(['first_name' => 'Kamala', 'last_name' => 'Khan', 'drupal_id' => '55555']);

        // Make some users with no Drupal ID.
        factory(User::class, 7)->create();

        // Make 5 duplicates for Tony Stark & Steve Rogers' Drupal IDs.
        foreach ([$tony->drupal_id, $steve->drupal_id] as $drupalId) {
            for ($i = 0; $i < 5; $i++) {
                User::forceCreate([
                    'first_name' => $this->faker->firstName,
                    'email' => $this->faker->email,
                    'drupal_id' => $drupalId,
                ]);
            }
        }

        // Make 5 users with explicitly null Drupal ID. Trouble-makers!
        for ($i = 0; $i < 5; $i++) {
            User::forceCreate([
                'first_name' => $this->faker->firstName,
                'email' => $this->faker->email,
                'drupal_id' => null,
            ]);
        }

        // There should be 20 users to start with...
        $this->assertEquals(25, User::all()->count(), 'created all the expected duplicates');

        // And after running the command, we should have only the 10 unique Drupal IDs.
        $this->artisan('northstar:clean_drupal_ids', ['--force' => true]);
        $this->assertEquals(15, User::all()->count(), 'removed the expected number of duplicates');

        // Finally, make sure that we kept all the right records.
        $this->assertEquals('Khan', User::where('drupal_id', $kamala->drupal_id)->first()->last_name);
        $this->assertEquals('Stark', User::where('drupal_id', $tony->drupal_id)->first()->last_name);
        $this->assertEquals('Rogers', User::where('drupal_id', $steve->drupal_id)->first()->last_name);
    }
}
