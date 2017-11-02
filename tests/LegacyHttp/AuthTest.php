<?php

use Northstar\Models\User;

class AuthTest extends BrowserKitTestCase
{
    /**
     * Test for logging in a user
     * POST /auth/verify
     *
     * @return void
     */
    public function testVerify()
    {
        User::create([
            'email' => 'verify-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'verify-test@dosomething.org',
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }

    /**
     * Test for logging in a user, but wildly!
     * POST /auth/verify
     *
     * @return void
     */
    public function testNormalizedVerify()
    {
        User::create([
            'email' => 'normalized-verify@dosomething.org',
            'password' => 'secret',
        ]);

        $this->withLegacyApiKeyScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'Normalized-Verify@dosomething.org ', // <-- a trailing space!? the nerve!
            'password' => 'secret',
        ]);

        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
            ],
        ]);
    }
}
