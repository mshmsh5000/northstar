<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use DoSomething\Gateway\Blink;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Models\User;

class BackfillCustomerIoProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio {start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send profiles updated after the given date to Customer.io';

    /**
     * Execute the console command.
     *
     * @param Blink $blink
     * @return mixed
     */
    public function handle(Blink $blink)
    {
        $start = new Carbon($this->argument('start'));

        // Iterate over users where the `mobile` field is not null
        // or their profile was updated after the given date.
        $query = User::whereNotNull('mobile')
            ->orWhere('updated_at', '>', $start);

        $query->chunkById(200, function (Collection $records) use ($blink) {
            $users = User::hydrate($records->toArray());

            // Send each of the loaded users to Blink's user queue.
            $users->each(function ($user) {
                gateway('blink')->userCreate($user->toBlinkPayload());
            });
        });
    }
}
