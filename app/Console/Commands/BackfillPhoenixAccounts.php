<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Northstar\Auth\Registrar;
use Northstar\Models\User;

class BackfillPhoenixAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:backfill_phoenix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill accounts for any users without a drupal_id.';

    /**
     * Execute the console command.
     *
     * @param Registrar $registrar
     * @return mixed
     */
    public function fire(Registrar $registrar)
    {
        // Find all users where the given field is stored as a string type.
        // @see: https://docs.mongodb.com/manual/reference/operator/query/type/#op._S_type
        $users = User::whereNull('drupal_id')
            ->whereNotNull('mobile')
            ->forPage(1, 100000)
            ->options(['allowDiskUse' => true])
            ->get();

        $this->comment('Found '.count($users).' with a mobile but no Phoenix account! :(');

        foreach ($users as $user) {
            /** @var User $user */
            $user = $registrar->createDrupalUser($user);
            $user->save();

            if ($user->drupal_id) {
                $this->info('Created/linked Phoenix account for '.$user->id.' → '.$user->drupal_id);
            } else {
                $this->warn('Could not create Phoenix account for '.$user->id);
            }
        }

        $this->line('Nice job, we\'re done! :)');
    }
}
