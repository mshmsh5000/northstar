<?php

use Northstar\Models\Client;
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

    /**
     * The Faker generator, for creating test data.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Setup the test environment. This is run before *every* single
     * test method, so avoid doing anything that takes too much time!
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Get a new Faker generator from Laravel.
        $this->faker = app(\Faker\Generator::class);

        // Reset the testing database & run migrations.
        $this->app->make('db')->getMongoDB()->drop();
        $this->artisan('migrate');
    }

    /**
     * Use the given API key for this request.
     *
     * @param Client $client
     * @return $this
     */
    public function withApiKey(Client $client)
    {
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_X-DS-REST-API-Key' => $client->client_secret,
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
        $client = Client::create([
            'app_id' => 'testing'.$this->faker->uuid,
            'scope' => $scopes,
        ]);

        $this->withApiKey($client);

        return $this;
    }

    /**
     * Set the currently logged in user for the application. Use this instead of Laravel's
     * built-in $this->actingAs() or $this->be() because it will create an actual token in
     * the database to be manipulated/checked & set proper authentication header.
     *
     * @param User $user
     * @return $this
     */
    public function asUser(User $user)
    {
        $token = $user->login();
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => 'Bearer '.$token->key,
        ]);

        return $this;
    }

    /**
     * Get the raw Mongo document for inspection.
     *
     * @param $collection - Mongo Collection name
     * @param $id - The _id of the document to fetch
     * @return array
     */
    public function getMongoDocument($collection, $id)
    {
        $document = $this->app->make('db')->collection($collection)->where(['_id' => $id])->first();

        $this->assertNotNull($document, sprintf(
            'Unable to find document in collection [%s] with _id [%s].', $collection, $id
        ));

        return $document;
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
