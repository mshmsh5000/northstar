<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Services\CustomerIo;
use Jenssegers\Mongodb\Eloquent\Builder;

class CustomerIoMobileUserImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio_mobile_import
                            {--dry : Perform a dry run of the command.}
                            {--not_backfilled : Only import records that have not already been backfilled.}
                            {--throughput= : The maximum number of records to process per minute.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data for user\'s with a mobile number to Customer.io';

    /**
     * Count of items to chunk the query by.
     *
     * @var integer
     */
    protected $chunkCount = 200;

    /**
     * Chunk iteration number.
     *
     * @var integer
     */
    protected $chunkNumber = 0;

    /**
     * The Customer Io service.
     *
     * @var \Northstar\Services\CustomerIo
     */
    protected $customerIo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->customerIo = new CustomerIo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('::: Script started on '.Carbon::now()->format('m/d/Y \a\t H:i:s'));

        $dryRun = $this->option('dry');
        $throughput = (int) $this->option('throughput');
        $notBackfilled = $this->option('not_backfilled');

        $query = User::whereNotNull('mobile');

        if ($notBackfilled) {
            // Only include user's that have not been backfilled to customer.io.
            $query->where('cio_backfilled', '!=', true);
        }

        $query->chunkById($this->chunkCount, function (Collection $users) use ($throughput, $dryRun) {
            $users->each(function (User $user, $index) use ($throughput, $dryRun) {
                $itemNumber = ($this->chunkCount * $this->chunkNumber) + ($index + 1);

                if ($dryRun) {
                    $this->line('Dry run to backfill user '.$user->id.' #'.$itemNumber);
                } else {
                    try {
                        // Send user data to CustomerIo.
                        // $this->customerIo->updateProfile($user);

                        // Mark this user as processed.
                        // $user->cio_backfilled = true;
                        // $user->save(['touch' => false]);

                        $this->line('Successfully backfilled user '.$user->id.' #'.$itemNumber);
                    } catch (Exception $e) {
                        $this->error('Failed to backfill user '.$user->id.' #'.$itemNumber);
                    }
                }

                // If the `--throughput #` parameter is set, make sure we can't process
                // more than # users per minute by taking a little nap after each user.
                throttle($throughput);
            });

            $this->chunkNumber += 1;
        });

        $this->info('::: Script ended on '.Carbon::now()->format('m/d/Y \a\t H:i:s'));
    }
}
