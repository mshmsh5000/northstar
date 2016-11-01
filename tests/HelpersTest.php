<?php

class HelpersTest extends TestCase
{
    /** @test */
    public function testRouteHasAttachedMiddleware()
    {
        $request = $this->get('/login');

        // It should be able to check if in a middleware group.
        $this->assertTrue(has_middleware('web'));
        $this->assertFalse(has_middleware('api'));

        // ...or just if it has any middleware at all!
        $this->assertTrue(has_middleware());
    }
}
