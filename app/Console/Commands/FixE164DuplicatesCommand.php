<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;

class FixE164DuplicatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:e164-dupes {column=mobile}
                            {--pretend : List the duplicates that would be deleted.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix duplicates created during E.164 conversion.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $pretending = $this->option('pretend');

        // Since new environments have proper uniqueness constraint
        // on 'mobile', we need to be able to override this.
        $mobileColumn = $this->argument('column');
        $originalColumn = '_old_mobile';

        /** @var \MongoDB\Database $mongo */
        $mongo = app('db')->getMongoDB();

        // Find the documents where the 'mobile' column has a duplicate.
        // credit: https://stackoverflow.com/a/35624773/811624
        $collection = $mongo->selectCollection('users');
        $duplicates = $collection->aggregate([
            [
                '$match' => [
                    $mobileColumn => ['$ne' => null],
                ],
            ],
            [
                '$group' => [
                    '_id' => [$mobileColumn => '$'.$mobileColumn],
                    'uniqueIds' => ['$addToSet' => '$_id'],
                    'count' => ['$sum' => 1],
                ],
            ],
            [
                '$match' => [
                    'count' => ['$gt' => 1],
                ],
            ],
        ], ['allowDiskUse' => true]);

        /** @var \MongoDB\Model\BSONDocument $duplicate */
        foreach ($duplicates as $duplicate) {
            // Recursively convert BSONDocument into an array.
            $duplicate = json_decode(json_encode($duplicate), true);

            $mobile = $duplicate['_id'][$mobileColumn];
            $ids = array_pluck($duplicate['uniqueIds'], '$oid');

            $this->comment('Found duplicates for '.$mobile);

            // When we find multiple IDs with the same E.164 'mobile' value,
            // destroy the ones that weren't normalized as we intended.
            $trash = User::find($ids)->filter(function ($user) use ($originalColumn) {
                return ! preg_match('#^[0-9]{10}$#', $user->{$originalColumn});
            });

            /** @var User $user */
            foreach ($trash as $user) {
                $verb = $pretending ? 'Will remove' : 'Removing';
                $this->line($verb.' duplicate user '.$user->id);

                if (! $pretending) {
                    $user->delete();
                }
            }
        }
    }
}
