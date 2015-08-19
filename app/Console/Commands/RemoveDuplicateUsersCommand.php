<?php namespace Northstar\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Northstar\Models\User;

class RemoveDuplicateUsersCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'users:dedupe';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Script to remove duplicate users.';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Find all duplicate users by email.
        $duplicates = User::raw(function($collection) {
            return $collection->aggregate(
                [
                    '$group' => [
                        '_id' => ['email' => '$email'
                        ],
                        'uniqueIds' => [
                            '$addToSet' => '$_id'
                        ],
                        'count' => [
                            '$sum' => 1
                        ]
                    ]
                ],
                [
                    '$match' => [
                        'count' => [
                            '$gt' => 1
                        ]
                    ]
                ]
            );
        });
        // For each duplicate user, delete all records except first created record.
        foreach ($duplicates['result'] as $user) {
                if (count($user['uniqueIds']) > 1) {
                    $duplicate_id = $user['uniqueIds'][0]->{'$id'};
                    User::destroy($duplicate_id);
                }
        }

        $this->info('Deduplication complete.');
    }
}
