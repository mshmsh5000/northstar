<?php

namespace Northstar\Events;

use Illuminate\Queue\SerializesModels;

class UserGotKudo extends Event
{
    use SerializesModels;

    public $reportback_item_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($reportback_item_id)
    {
        $this->reportback_item_id = $reportback_item_id;
    }
}
