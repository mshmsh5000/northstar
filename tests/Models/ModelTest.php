<?php

use Northstar\Models\User;

class ModelTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_unset_null_fields()
    {
        /** @var User $user */
        $user = factory(User::class)->create([
            'mobile' => $this->faker->phoneNumber,
            'last_name' => null,
        ]);

        // Now, unset some fields.
        $user->mobile = null;
        $user->last_name = null;
        $user->save();

        // Make sure the field is unset on the actual document.
        $document = $this->getMongoDocument('users', $user->id);
        $this->assertArrayNotHasKey('mobile', $document);
        $this->assertArrayNotHasKey('last_name', $document);
    }
}
