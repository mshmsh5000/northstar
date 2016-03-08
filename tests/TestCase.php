<?php

use Northstar\Models\ApiKey;

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
     *
     *
     * @param array $scopes
     * @return $this
     */
    public function withAuthorizedScopes(array $scopes)
    {
        $key = ApiKey::create([
            'app_id' => 'testing'.time(),
            'scope' => $scopes
        ]);

        $this->withApiKey($key);

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
