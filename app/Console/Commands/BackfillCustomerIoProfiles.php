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
    protected $signature = 'northstar:cio
                            {start : The date to begin back-filling records from.}
                            {end=now : The date to back-fill records until.}
                            {--created_at : Process records based on their created_at timestamp.}
                            {--throughput= : The maximum number of records to process per minute.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send profiles updated after the given date to Customer.io';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $start = new Carbon($this->argument('start'));
        $end = new Carbon($this->argument('end'));

        $byCreatedAt = $this->option('created_at');
        $throughput = $this->option('throughput');

        if ($byCreatedAt) {
            // If we pass `--created_at` flag, iterate over all users created in that time frame.
            $query = User::where(function (Builder $query) use ($start, $end) {
                $query->where('created_at', '>', $start)->where('created_at', '<', $end);
            });
        } else {
            // Iterate over users where the `mobile` field is not null (we skipped originally) or their
            // profile was updated in the given time frame, skipping ones we have already backfilled.
            $query = User::where(function (Builder $query) use ($start, $end) {
                $query->whereNotNull('mobile')->orWhere('updated_at', '>', $start);
            })->where('updated_at', '<', $end)->where('cio_backfilled', '!=', true);
        }

        $query->chunkById(200, function (Collection $users) use ($throughput) {
            // Send each of the loaded users to Blink's user queue.
            $users->each(function (User $user) use ($throughput) {
                try {
                    gateway('blink')->userCreate($user->toBlinkPayload());

                    // Mark this user as processed.
                    $user->cio_backfilled = true;
                    $user->save(['touch' => false]);

                    $this->line('Successfully backfilled user '.$user->id);
                } catch (Exception $e) {
                    $this->error('Failed to backfill user '.$user->id);
                }

                // If the `--throughput #` parameter is set, make sure we can't
                // process more than # users per minute by taking a little nap.
                if ($throughput) {
                    $seconds = 60 / $throughput;
                    usleep($seconds * 1000000);
                }
            });
        });
    }
}
