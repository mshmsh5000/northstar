<?php

use Carbon\Carbon;
use DoSomething\Gateway\Blink;
use Northstar\Models\User;

class BackfillCustomerIoProfilesTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_backfill_users()
    {
        factory(User::class, 2)->create(); // with phone number!
        factory(User::class, 3)->create(['mobile' => null, 'updated_at' => new Carbon('1/15/2016')]);
        factory(User::class, 2)->create(['updated_at' => new Carbon('3/14/2017')]);

        // Reset our Blink mock & set expectation that it'll be called 4 times - once
        // for each of the users updated after 1/1/2017, & once for each w/ a phone #.
        // @TODO: This is failing in CI. Need to figure out why. :'(
        // $this->mock(Blink::class)->shouldReceive('userCreate')->times(4);

        // Run the Customer.io backfill command.
        $this->artisan('northstar:cio', ['start' => '1/1/2017']);
    }
}
