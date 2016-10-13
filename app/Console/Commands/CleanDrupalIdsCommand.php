<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Jenssegers\Mongodb\Collection;
use Northstar\Models\User;

class CleanDrupalIdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:clean_drupal_ids
                            {--pretend : List the duplicates that would be deleted.}
                            {--aurora=https://aurora.dosomething.org : The Aurora URL to link to.}
                            {--force : Do not ask for confirmation on dupes we\'re uncertain about.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove users with duplicated Drupal ID fields.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Find all duplicate users by Drupal ID.
        $blanks = User::raw(function (Collection $collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'drupal_id' => ['$exists' => true],
                    ],
                ],
                [
                    '$group' => [
                        '_id' => ['drupal_id' => '$drupal_id'],
                        'uniqueIds' => ['$addToSet' => '$_id'],
                        'count' => ['$sum' => 1],
                    ],
                ],
                [
                    '$match' => [
                        '_id' => ['$ne' => 'null'],
                        'count' => ['$gt' => 1],
                    ],
                ],
            ], [
                'allowDiskUse' => true,
            ]);
        });

        $app = $this;
        $blanks->each(function ($this) use (&$app) {
            $app->info('Found '.$this['count'].' duplicates for '. $this['_id']['drupal_id'] .' ('.config('services.drupal.url').'/user/'. $this['_id']['drupal_id'].'):');

            // Load each duplicated user model, sort them by their created_at, and reset keys.
            $users = User::findMany($this['uniqueIds']->bsonSerialize())
              ->sortBy('created_at')->values();

            $users->each(function ($user, $index) {
                $aurora = $this->option('aurora');

                // If the Drupal ID is explicitly set null, unset that field & don't delete.
                if (is_null($user->drupal_id)) {
                    $user->unset('drupal_id');
                    $user->save();

                    return;
                }

                // We want to delete all but the oldest (sorted first) dupe.
                if ($index === 0) {
                    $this->line('Keeping user account: '.$aurora.'/users/'.$user->id.' ('.$user->email.' / '.$user->first_name.')');

                    return;
                }

                // Delete the user automatically if they don't have a first name. Otherwise we'll prompt.
                $safeToDelete = empty($user->first_name) || $this->option('force');
                $verb = $safeToDelete ? 'Will delete' : 'Would ask to delete';

                // If we're pretending, skip over actually deleting the user.
                if (! $this->option('pretend')) {
                    $verb = 'Did not delete';

                    if ($safeToDelete || $this->confirm('Are you sure that we should delete '.$aurora.'/users/'.$user->id.'?')) {
                        $user->delete();
                        $verb = 'Deleted';
                    }
                }

                $this->comment($verb.' duplicate: '.$aurora.'/users/'.$user->id.' ('.$user->email.' / '.$user->first_name.')');
            });

            $app->line('');

        });

    }
}
