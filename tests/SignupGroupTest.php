<?php

class SignupGroupTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        Artisan::call('migrate');
        $this->seed();

        // Prepare server headers
        $this->server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_Accept' => 'application/json',
            'HTTP_X-DS-Application-Id' => '456',
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
        ];
    }

    public function testShow()
    {
        $response = $this->call('GET', 'v1/signup-group/100', [], [], [], $this->server);
        $content = json_decode($response->getContent(), true);
        $data = $content['data'];

        // Verify it's the two users we expect it to be.
        $usersFound = 0;
        for ($i = 0; $i < count($data['users']); $i++) {
            $email = $data['users'][$i]['email'];
            if ($email == 'test1@dosomething.org' || $email == 'test3@dosomething.org') {
                $usersFound++;
            }
        }

        $this->assertEquals(2, $usersFound);

        // Campaign ID should be 123.
        $this->assertEquals(123, $data['campaign_id']);
    }
}
