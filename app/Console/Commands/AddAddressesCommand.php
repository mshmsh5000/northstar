<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Illuminate\Console\Command;
use Northstar\Models\User;

class AddAddressesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:addr {path} {--skip=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add the provided addresses to Northstar accounts.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = base_path($this->argument('path'));
        $skip = $this->option('skip');

        $reader = Reader::createFromPath($path);
        foreach ($reader->fetchAssoc($skip) as $index => $row) {
            $user = User::find($row['northstar_id']);

            if (! $user) {
                $this->warn('Could not find user for '.$row['northstar_id'].'.');

                continue;
            }

            // Update the user with the given address fields from the CSV, replacing any
            // provided 'NA' values with actual nulls.
            $addressFields = collect($row)
                ->only(['addr_street1', 'addr_street2', 'addr_city', 'addr_state', 'addr_zip', 'country'])
                ->map(function ($value) {
                    return $value === 'NA' ? null : $value;
                });

            // NOTE: Blink updates are currently disabled for CLI scripts (which is
            // helpful for making this super quick & easy!). We'll send the CSV to
            // the Customer.io folks for them to apply separately on their end.
            $user->update($addressFields->toArray());

            $this->info('Updated address for '.$row['northstar_id'].'.');
        }
    }
}
