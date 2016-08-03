<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Jenssegers\Mongodb\Collection;
use Northstar\Models\User;

class CleanDrupalIdsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'northstar:clean_drupal_ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up users with empty Drupal ID fields.';

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
                    '$sort' => [
                        'created_at' => -1,
                    ],
                ],
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

        $blanks->each(function ($result) {
            $this->info('Found '.$result->count.' duplicates for '.$result->_id['drupal_id'].'.');

            foreach ($result->uniqueIds as $index => $duplicateId) {
                $user = User::find($duplicateId);

                if ($index !== 0) {
                    $user->delete();
                    $this->comment('Deleted duplicate with ID '.$user->id.'!');
                }
            }
        });
    }
}
