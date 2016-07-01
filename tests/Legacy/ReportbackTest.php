<?php

use Northstar\Models\User;
use Northstar\Services\Phoenix;

class ReportbackTest extends TestCase
{
    /**
     * Test for submitting a new campaign report back.
     * POST /user/campaigns/:nid/reportback
     *
     * @return void
     */
    public function testSubmitCampaignReportback()
    {
        $user = User::create(['drupal_id' => '512312']);

        // For testing, we'll mock successful Phoenix API responses.
        $phoenix = $this->mock(Phoenix::class);
        $phoenix->shouldReceive('createReportback')->once()->andReturn(['127']);
        $phoenix->shouldReceive('getReportback')->once()->andReturn([
            'data' => [
                'id' => 127,
                // ...
            ],
        ]);

        $this->asUser($user)->withScopes(['user'])->json('POST', 'v1/reportbacks', [
            'campaign_id' => 123,
            'quantity' => 10,
            'why_participated' => 'I love helping others',
            'file' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAMCA',
            'caption' => 'Here I am helping others.',
        ]);

        // The response should return a 200 OK status code
        $this->assertResponseStatus(200);
        $this->seeJson();
    }
}
