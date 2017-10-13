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
     * @return mixed
     */
    public function handle()
    {
        $start = new Carbon($this->argument('start'));

        // Iterate over users where the `mobile` field is not null (we skipped originally) or their
        // profile was updated after the given date, skipping ones we have already backfilled.
        $query = User::where(function (Builder $query) use ($start) {
            $query->whereNotNull('mobile')->orWhere('updated_at', '>', $start);
        })->where('cio_backfilled', '!=', true);

        $query->chunkById(200, function (Collection $users) {
            // Send each of the loaded users to Blink's user queue.
            $users->each(function (User $user) {
                try {
                    gateway('blink')->userCreate($user->toBlinkPayload());

                    // Mark this user as processed.
                    $user->cio_backfilled = true;
                    $user->save(['touch' => false]);

                    $this->line('Successfully backfilled user '.$user->id);
                } catch (Exception $e) {
                    $this->error('Failed to backfill user '.$user->id);
                }
            });
        });
    }
}
