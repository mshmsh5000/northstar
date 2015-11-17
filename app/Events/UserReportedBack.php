<?php

namespace Northstar\Events;

use Illuminate\Queue\SerializesModels;
use Northstar\Models\User;
use Northstar\Models\Campaign;

class UserReportedBack extends Event
{
    use SerializesModels;

    public $user;
    public $campaign;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, Campaign $campaign)
    {
        $this->user = $user;
        $this->campaign = $campaign;
    }
}
