<?php

use Northstar\Models\ApiKey;
use Northstar\Models\User;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Additional server variables for the request.
     *
     * @var array
     */
    protected $serverVariables = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_Accept' => 'application/json',
    ];

    public function setUp()
    {
        parent::setUp();

        // Migrate & seed a fresh copy of the database before each test case.
        $this->artisan('migrate');
        $this->seed();
    }

    /**
     * Use the given API key for this request.
     *
     * @param ApiKey $key
     * @return $this
     */
    public function withApiKey(ApiKey $key)
    {
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_X-DS-REST-API-Key' => $key->api_key,
        ]);

        return $this;
    }

    /**
     * Set an API key with the given scopes on the request.
     *
     * @param array $scopes
     * @return $this
     */
    public function withScopes(array $scopes)
    {
        $key = ApiKey::create([
            'app_id' => 'testing'.time(),
            'scope' => $scopes
        ]);

        $this->withApiKey($key);

        return $this;
    }

    /**
     * Set the currently logged in user for the application. Use this instead of Laravel's
     * built-in $this->actingAs() or $this->be() because it will create an actual token in
     * the database to be manipulated/checked & set proper authentication header.
     *
     * @param User $user
     * @param  string|null $driver
     * @return $this
     */
    public function asUser(User $user, $driver = null)
    {
        $token = $user->login();
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => 'Bearer '.$token->key
        ]);

        return $this;
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    /**
     * Mock a class, and register with the IoC container.
     *
     * @param $class String - Class name to mock
     * @return \Mockery\MockInterface
     */
    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }
}
