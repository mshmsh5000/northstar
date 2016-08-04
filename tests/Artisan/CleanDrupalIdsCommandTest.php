<?php

use Northstar\Models\User;

class CleanDrupalIdsCommandTest extends TestCase
{
    /**
     * Test that it does the thing its supposed to.
     * GET /users
     *
     * @return void
     */
    public function testThatItDeletesTheDupes()
    {
        User::create(['first_name' => 'Tony', 'last_name' => 'Stark', 'drupal_id' => '12345']);
        User::create(['first_name' => 'Steve', 'last_name' => 'Rogers', 'drupal_id' => '12346']);
        User::create(['first_name' => $this->faker->firstName, 'drupal_id' => '55555']);

        // Make some users with no Drupal ID.
        factory(User::class, 7)->create();

        // Make 5 duplicates for Tony Stark & Steve Rogers' Drupal IDs.
        foreach (['12345', '12346'] as $drupalId) {
            for ($i = 0; $i < 5; $i++) {
                User::create([
                    'first_name' => $this->faker->firstName,
                    'email' => $this->faker->email,
                    'drupal_id' => $drupalId,
                ]);
            }
        }

        // There should be 18 users to start with...
        $this->assertEquals(20, User::all()->count(), 'created all the expected duplicates');

        // And after running the command, we should have only the 10 unique Drupal IDs.
        $this->artisan('northstar:clean_drupal_ids');
        $this->assertEquals(10, User::all()->count(), 'removed the expected number of duplicates');

        // Finally, make sure those original two records for the duped Drupal IDs are the ones we kept.
        $this->assertEquals('Stark', User::where('drupal_id', '12345')->first()->last_name);
        $this->assertEquals('Rogers', User::where('drupal_id', '12346')->first()->last_name);
    }
}
