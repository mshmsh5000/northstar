<?php

use Northstar\Models\User;

class ConvertMobilesCommandTest extends TestCase
{
    /** @test */
    public function it_should_convert_numbers()
    {
        // Create users with some pre-generated numbers.
        $user1 = factory(User::class)->create(['mobile' => '7455559417']);
        $user2 = factory(User::class)->create(['mobile' => '6965552100']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->assertEquals('+17455559417', $user1->fresh()->e164);
        $this->assertEquals('+16965552100', $user2->fresh()->e164);
    }

    /** @test */
    public function it_should_handle_invalid_mobiles()
    {
        $user = factory(User::class)->create(['mobile' => '3', 'email' => null]);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->assertEquals(null, $user->fresh()->e164);
        $this->assertNotNull($user->fresh()->email);
    }

    /** @test */
    public function it_should_ignore_users_without_mobiles()
    {
        $user = factory(User::class)->create(['mobile' => null]);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->assertEquals(null, $user->fresh()->e164);
    }
}
