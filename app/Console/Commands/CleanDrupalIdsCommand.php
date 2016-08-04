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
    protected $signature = 'northstar:clean_drupal_ids {--pretend : List the duplicates that would be deleted.}';

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

        foreach ($blanks['result'] as $result) {
            $this->info('Found '.$result['count'].' duplicates for '.$result['_id']['drupal_id'].' ('.config('services.drupal.url').'/users/'.$result['_id']['drupal_id'].'):');

            // Load each duplicated user model, sort them by their created_at, and reset keys.
            $users = User::findMany($result['uniqueIds'])
                ->sortBy('created_at')->values();

            $users->each(function ($user, $index) {
                // If the Drupal ID is explicitly set null, unset that field & don't delete.
                if (is_null($user->drupal_id)) {
                    $user->unset('drupal_id');
                    $user->save();

                    return;
                }

                // We want to delete all but the oldest (sorted first) dupe.
                if ($index === 0) {
                    $this->comment('Keeping user account: http://aurora.dosomething.org/users/'.$user->id.' ('.$user->email.' / '.$user->first_name.')');

                    return;
                }

                $shouldDelete = ! $this->option('pretend');
                if ($shouldDelete) {
                    $user->delete();
                }

                $verb = $shouldDelete ? 'Deleted' : 'Will delete';
                $this->comment($verb.' duplicate: http://aurora.dosomething.org/users/'.$user->id.' ('.$user->email.' / '.$user->first_name.')');
            });

            $this->line('');
        }
    }
}
