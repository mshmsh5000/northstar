<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;
use Northstar\Models\User;

class FixMongoDatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:fix_mongo_dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert any string dates to ISODate objects.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $dateFields = ['created_at', 'updated_at', 'birthdate'];

        foreach ($dateFields as $field) {
            $this->reformatField($field);
        }
    }

    /**
     * Find records where the given field is a string, and
     * re-cast as an ISODate.
     *
     * @param $field
     */
    public function reformatField($field)
    {

        // Find all users where the given field is stored as a string type.
        // @see: https://docs.mongodb.com/manual/reference/operator/query/type/#op._S_type
        $users = User::where($field, 'type', 2)->forPage(1, 100000)->options(['allowDiskUse' => true])->get();

        foreach ($users as $user) {
            /** @var \Carbon\Carbon $carbon */
            $carbon = $user->{$field};

            /** @var \Jenssegers\Mongodb\Query\Builder $collection */
            $collection = app('db')->collection('users');
            $success = $collection
                ->where('_id', $user->id)
                ->update([$field => new UTCDateTime($carbon->getTimestamp() * 1000)]);

            if ($success === 1) {
                $this->info('Updated `'.$field.'` field for '.$user->id.'!');
            } else {
                $this->warn('Could not update `'.$field.'` field for '.$user->id.'!');
            }
        }
    }
}
