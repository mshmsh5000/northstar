<?php

use Northstar\Models\User;

class UserModelTest extends TestCase
{
    /** @test */
    public function it_should_send_new_users_to_blink()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        // We should have made one "create" request to Blink.
        $this->blinkMock->shouldHaveReceived('userCreate')->once()->with([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'mobile' => null,
            'mobile_status' => $user->mobilecommons_status,
            'addr_city' => $user->addr_city,
            'addr_state' => $user->addr_state,
            'addr_zip' => $user->addr_zip,
            'country' => $user->country,
            'language' => $user->language,
            'source' => $user->source,
            'source_detail' => $user->source_detail,
            'last_authenticated_at' => null,
            'updated_at' => $user->updated_at->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
        ]);
    }
}
