<?php

use Northstar\Models\User;

class FixSourcesCommandTest extends TestCase
{
    /** @test */
    public function it_should_fix_incorrect_sources()
    {
        // Make a ton of test accounts (without Drupal IDs).
        $one = factory(User::class)->create(['_id' => '57c9b53e42a0646a1b8b4670', 'source' => 'sms']);
        $two = factory(User::class)->create(['_id' => '57c9bbdc42a064b91b8b472d', 'source' => 'sms']);
        $three = factory(User::class)->create(['_id' => '57c9bbdb42a064b91b8b472b', 'source' => 'sms']);
        $sms = factory(User::class)->create([
            '_id' => '57c9be8842a064c81b8b462d',
            'source' => 'sms',
            'created_at' => '2013-05-01',
        ]);

        // Run the magic command on our messy data.
        $this->artisan('northstar:sources', ['path' => 'tests/Console/example-sources.csv']);

        // After running the command, the given users should have 'niche' source.
        $this->assertEquals('niche', $one->fresh()->source);
        $this->assertEquals('niche', $two->fresh()->source);
        $this->assertEquals('niche', $three->fresh()->source);

        // ...unless their 'created_at' was way before the Niche import timestamp.
        $this->assertEquals('sms', $sms->fresh()->source);
    }
}
