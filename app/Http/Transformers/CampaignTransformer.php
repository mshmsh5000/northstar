<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\Campaign;
use League\Fractal\TransformerAbstract;

class CampaignTransformer extends TransformerAbstract
{
    /**
     * @param Campaign $campaign
     * @return array
     */
    public function transform(Campaign $campaign)
    {
        return [
            'drupal_id' => $campaign->drupal_id,

            'reportback_id' => $campaign->reportback_id,
            'reportback_source' => $campaign->reportback_source,
            'reportback_data' => $campaign->reportback_data,

            'signup_id' => $campaign->signup_id,
            'signup_source' => $campaign->signup_source,

            'signup_group' => $campaign->signup_group,
        ];
    }
}
