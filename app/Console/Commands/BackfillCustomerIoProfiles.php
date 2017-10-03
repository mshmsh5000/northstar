<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use DoSomething\Gateway\Blink;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
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

        // Iterate over users who we have not already backfilled, and where the `mobile` field
        // is not null (we skipped originally) or their profile was updated after the given date.
        $query = User::whereNull('cio_backfilled')->where(function (Builder $query) use ($start) {
            $query->whereNotNull('mobile')
                  ->orWhere('updated_at', '>', $start);
        });

        $query->chunkById(200, function (Collection $records) use ($blink) {
            $users = User::hydrate($records->toArray());

            // Send each of the loaded users to Blink's user queue.
            $users->each(function (User $user) {
                try {
                    gateway('blink')->userCreate($user->toBlinkPayload());

                    // Mark this user as processed.
                    $user->cio_backfilled = true;
                    $user->save();

                    $this->line('Successfully backfilled user '.$user->id);
                } catch (Exception $e) {
                    $this->error('Failed to backfill user '.$user->id);
                }
            });
        });
    }
}
