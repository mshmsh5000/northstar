<?php

use Northstar\Models\User;

class ConvertMobilesCommandTest extends TestCase
{
    /** @test */
    public function it_should_convert_numbers()
    {
        $this->createMongoDocument('users', ['mobile' => '7455559417']);
        $this->createMongoDocument('users', ['mobile' => '6965552100']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->seeInDatabase('users', ['mobile' => '7455559417', 'e164' => '+17455559417']);
        $this->seeInDatabase('users', ['mobile' => '6965552100', 'e164' => '+16965552100']);
    }

    /** @test */
    public function it_should_handle_invalid_mobiles()
    {
        $this->createMongoDocument('users', ['mobile' => '3']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $user = User::first();
        $this->assertEquals(null, $user->e164);
        $this->assertNotNull($user->email);
    }

    /** @test */
    public function it_should_ignore_users_without_mobiles()
    {
        $user = factory(User::class)->create(['mobile' => null]);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->assertEquals(null, $user->fresh()->e164);
    }

    /** @test */
    public function it_should_restart_based_on_skip_argument()
    {
        $this->createMongoDocument('users', ['mobile' => '7455559417']);
        $this->createMongoDocument('users', ['mobile' => '6965552100']);
        $this->createMongoDocument('users', ['mobile' => '2225559999']); // start!
        $this->createMongoDocument('users', ['mobile' => '8145551234']);

        // Run the command to convert to E.164 format.
        $id = User::where('mobile', '2225559999')->first()->id;
        $this->artisan('northstar:e164', ['start' => $id]);

        $this->seeInDatabase('users', ['mobile' => '7455559417', 'e164' => null]); // skipped!
        $this->seeInDatabase('users', ['mobile' => '6965552100', 'e164' => null]); // skipped!
        $this->seeInDatabase('users', ['mobile' => '8145551234', 'e164' => '+18145551234']);
        $this->seeInDatabase('users', ['mobile' => '2225559999', 'e164' => '+12225559999']);
    }
}
