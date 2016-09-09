<?php

use Northstar\Models\User;

class WebTest extends TestCase
{
    /**
     * Reset the server variables for the request.
     *
     * @var array
     */
    protected $serverVariables = [
        'Accept' => 'text/html',
    ];

    /**
     * Test that the homepage redirects to login page.
     */
    public function testHomepageAnonymousRedirect()
    {
        $this->get('/')->followRedirects();

        $this->seePageIs('login');
    }

    /**
     * Test that the homepage renders for logged-in users.
     */
    public function testHomepage()
    {
        $user = factory(User::class)->create();
        $this->be($user, 'web');

        $this->visit('/');

        $this->assertResponseOk();
    }

    /**
     * Test that users can log in via the web.
     */
    public function testLogin()
    {
        $user = factory(User::class)->create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);

        $this->visit('login')
            ->type('Login-Test@dosomething.org', 'username')
            ->type('secret', 'password')
            ->press('Log In');

        $this->assertEquals($user->id, auth()->id());
        $this->see('You\'re logged in.');
    }

    /**
     * Test that users cannot login with bad credentials via the web.
     */
    public function testLoginWithInvalidCredentials()
    {
        factory(User::class)->create(['email' => 'login-test@dosomething.org', 'password' => 'secret']);

        $this->visit('login')
            ->type('Login-Test@dosomething.org', 'username')
            ->type('open-sesame', 'password') // <-- wrong password!
            ->press('Log In');

        $this->see('These credentials do not match our records.');
    }

    /**
     * Test that an authenticated user can log out.
     */
    public function testLogout()
    {
        $user = factory(User::class)->create();
        $this->be($user, 'web');

        $this->get('logout')->followRedirects();
        $this->see('Log in to get started!');

        $this->assertEquals(false, auth()->check());
    }
}
