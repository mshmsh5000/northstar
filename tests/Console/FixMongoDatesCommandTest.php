<?php

use MongoDB\BSON\UTCDateTime;

class FixMongoDatesCommandTest extends TestCase
{
    /**
     * Test that it does the thing its supposed to.
     * GET /users
     *
     * @return void
     */
    public function testThatItDoesTheThings()
    {
        /** @var \Jenssegers\Mongodb\Query\Builder $collection */
        $collection = app('db')->collection('users');
        $collection->insert([
            ['first_name' => 'Bob', 'birthdate' => '10/25/1990'],
            ['first_name' => 'Phil', 'updated_at' => new UTCDateTime('1472809248000'), 'created_at' => '2016-12-06T13:58:59+0000'],
            ['first_name' => 'Luis', 'updated_at' => '2016-11-01T13:58:59+0000', 'created_at' => '2016-11-05T13:58:59+0000'],
        ]);

        // Run the magic command on our messy data.
        $this->artisan('northstar:fix_mongo_dates');

        // We should now see only nicely formatted UTCDateTimes!
        $this->seeInDatabase('users', ['first_name' => 'Bob', 'birthdate' => new UTCDateTime('656812800000')]);
        $this->seeInDatabase('users', ['first_name' => 'Phil', 'updated_at' => new UTCDateTime('1472809248000'), 'created_at' => new UTCDateTime('1481032739000')]);
        $this->seeInDatabase('users', ['first_name' => 'Luis', 'updated_at' => new UTCDateTime('1478008739000'), 'created_at' => new UTCDateTime('1478354339000')]);
    }
}
