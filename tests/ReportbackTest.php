<?php

use Northstar\Models\User;
use Northstar\Services\Phoenix;

class ReportbackTest extends TestCase
{
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
     * Headers for a typical user.
     * @var array
     */
    protected $server;

    /**
     * Headers for a user who has already signed up.
     * @var array
     */
    protected $signedUpServer;

    /**
     * Headers for a user who has already reported back.
     * @var array
     */
    protected $reportedBackServer;

    /**
     * Migrate database and set HTTP headers.
     */
    public function setUp()
    {
        parent::setUp();

        // Migrate & seed database
        $this->artisan('migrate');
        $this->seed();

        // Prepare server headers
        $this->server = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('5430e850dt8hbc541c37tt3d')->login()->key,
        ];

        $this->signedUpServer = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('5480c950bffebc651c8b456f')->login()->key,
        ];

        $this->reportedBackServer = [
            'HTTP_X-DS-REST-API-Key' => 'abc4324',
            'HTTP_Authorization' => 'Bearer '.User::find('bf1039b0271bcc636aa5477a')->login()->key,
        ];
    }

    /**
     * Test for submitting a new campaign report back.
     * POST /user/campaigns/:nid/reportback
     *
     * @return void
     */
    public function testSubmitCampaignReportback()
    {
        $payload = [
            'campaign_id' => 123,
            'quantity' => 10,
            'why_participated' => 'I love helping others',
            'file' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCA',
            'caption' => 'Here I am helping others.',
        ];

        // For testing, we'll mock a successful Phoenix API response.
        $this->mock(Phoenix::class)->shouldReceive('createReportback')->once()->andReturn([
            '127',
        ]);

        $response = $this->call('POST', 'v1/reportbacks', [], [], [], $this->signedUpServer, json_encode($payload));

        // The response should return a 200 OK status code
        $this->assertEquals(200, $response->getStatusCode());

        // Response should be valid JSON
        $content = $response->getContent();
        $this->assertJson($content);
    }
}
