<?php

use Northstar\Models\User;

class ThrottleRequestsTest extends TestCase
{
    /**
     * Test that anonymous requests are heavily rate limited.
     * GET /users
     *
     * @return void
     */
    public function testThrottlesAnonymousRequests()
    {
        // Let's just get 48 requests out of the way.
        for ($i = 1; $i <= 49; $i++) {
            $this->get('v1/scopes');
        }

        // Since these requests are made anonymously, they should be limited to 50/hr.
        $this->seeHeader('X-RateLimit-Limit', 50);

        // The 50th request should be a-okay, but...
        $this->get('v1/scopes');
        $this->assertResponseStatus(200);

        // The 51st request should be refused.
        $this->get('v1/scopes');
        $this->assertResponseStatus(429);
    }

    /**
     * Test that authenticated requests are rate limited.
     * GET /users
     *
     * @return void
     */
    public function testThrottlesAuthenticatedRequests()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user'])->get('v1/profile');

        // See that we have the "authenticated" limit of 5000/hr per token.
        $this->seeHeader('X-RateLimit-Limit', 5000);
        $this->seeHeader('X-RateLimit-Remaining', 4999);

        // Using a different access token for the same user should share the same rate limit.
        $this->asUser($user, ['user'])->get('v1/profile');
        $this->seeHeader('X-RateLimit-Remaining', 4998);
    }

    /**
     * Test that requests with 'unlimited' scope are not rate limited.
     * GET /users
     *
     * @return void
     */
    public function testDoesNotThrottleUnlimitedScope()
    {
        $user = factory(User::class)->create();
        $this->asUser($user, ['unlimited'])->get('v1/scopes');

        // See that we are not rate limited.
        $this->assertFalse($this->response->headers->has('X-RateLimit-Limit'));
    }
}
