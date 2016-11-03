<?php

use Northstar\Auth\Registrar;
use Northstar\Models\User;

class PasswordResetTest extends TestCase
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
    public function testPasswordResetFlow()
    {
        $user = factory(User::class)->create(['email' => 'forgetful@example.com']);
        $token = '';

        // We'll mock the email for this request & store the token for later.
        // @TODO: Use Laravel 5.3's Mail Fakes for this. (https://laravel.com/docs/5.3/mocking#mail-fakes)
        Mail::shouldReceive('send')->once()->with('auth.emails.password', Mockery::on(function ($array) use (&$user, &$token) {
            $token = $array['token'];

            return $user->id == $array['user']->id;
        }), Mockery::any());

        // The user should be able to request a new password by entering their email.
        $this->visit('/password/reset');
        $this->see('Forgot your password?');
        $this->submitForm('Request New Password', [
            'email' => 'forgetful@example.com',
        ]);

        // The user should visit the link that was sent via email & set a new password.
        $this->visit('/password/reset/'.$token.'?email='.$user->email);
        $this->postForm('Reset Password', [
            'password' => 'top_secret',
            'password_confirmation' => 'top_secret',
        ]);

        // The user should be logged-in to Northstar, and redirected to Phoenix's OAuth flow.
        $this->seeIsAuthenticatedAs($user, 'web');
        $this->assertRedirectedTo('http://dev.dosomething.org:8888/user/authorize');

        // And their account should be updated with their new password.
        $this->assertTrue(app(Registrar::class)->validateCredentials($user->fresh(), ['password' => 'top_secret']));
    }
}
