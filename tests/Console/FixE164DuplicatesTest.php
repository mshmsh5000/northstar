<?php

class FixE164DuplicatesTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_remove_duplicates()
    {
        // For the test, we'll put our "duplicates" in `new_mobile`.
        $this->createMongoDocument('users', ['_old_mobile' => '7455559417', 'new_mobile' => '+17455559417']);
        $this->createMongoDocument('users', ['_old_mobile' => '6965552100', 'new_mobile' => '+16965552100']);
        $this->createMongoDocument('users', ['_old_mobile' => '16965552100', 'new_mobile' => '+16965552100']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164-dupes', ['column' => 'new_mobile']);

        $this->seeInDatabase('users', ['_old_mobile' => '7455559417', 'new_mobile' => '+17455559417']);
        $this->seeInDatabase('users', ['_old_mobile' => '6965552100', 'new_mobile' => '+16965552100']);
        $this->notSeeInDatabase('users', ['_old_mobile' => '16965552100']);
    }
}
