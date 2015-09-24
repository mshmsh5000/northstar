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
        $email_duplicates = User::raw(function($collection) {
            return $collection->aggregate(
                [
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
                ],
                [
                    'allowDiskUse' => true
                ]
            );
        });

        // Delete email duplicates.
           $this->deduplicate($email_duplicates);

        // Find all duplicate users by mobile.
        $mobile_duplicates = User::raw(function($collection) {
            return $collection->aggregate(
                [
                    [
                        '$group' => [
                            '_id' => ['mobile' => '$mobile'
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
                ],
                [
                    'allowDiskUse' => true
                ]
            );
        });

        // Delete mobile duplicates.
            $this->deduplicate($mobile_duplicates);

        $this->info('Deduplication complete.');
    }

    // Combine all user info. into one record and delete all duplicates.
        public function deduplicate($duplicates) {
            foreach ($duplicates['result'] as $user) {
                $length = $user['count'];

                if (isset($user['_id']['email'])) {
                    for ($i = 0; $i < $length-1; $i++) {
                        if (count($user['uniqueIds']) > 1) {
                            $duplicate_id = $user['uniqueIds'][$i]->{'$id'};
                            $second_user = User::where('_id', '=', $duplicate_id)->first();
                            $first_user = User::where('_id', '=', $user['uniqueIds'][$i+1]->{'$id'})->first();
                            dd($second_user->created_at);
                            $updated_user = array_merge(array_filter($second_user->toArray()), array_filter($first_user->toArray()));
                            // $user = User::where('email', '=', $email)->first();
                            //     $updated_user = array_merge(array_filter($second_user->toArray()), array_filter($first_user->toArray()));

                            User::destroy($duplicate_id);
                            echo "user deleted: " . $user['_id']['email'] . " " . $duplicate_id . "\n";
                        }
                    }
                } elseif (isset($user['_id']['mobile'])) {
                    for ($i = 0; $i < $length-1; $i++) {
                        if (count($user['uniqueIds']) > 1) {
                            $duplicate_id = $user['uniqueIds'][$i]->{'$id'};
                            User::destroy($duplicate_id);
                            echo "user deleted: " . $user['_id']['mobile'] . " " . $duplicate_id . "\n";
                        }
                    }
                }

            }
        }
}
    /**
     * Combine fields with information from first created user and delete duplicate records.
     */
    // public function combine($first_user, $second_user)
    // {
    //     // Always make sure $first_user is the "original" user that we're going merge.
    //     if ($first_user->created_at > $second_user->created_at) {
    //         $tmp = $second_user;
    //         $second_user = $first_user;
    //         $first_user = $tmp;
    //     }
    //     // Merge their data and save to the first user
    //     $updated_user = array_merge(array_filter($second_user->toArray()), array_filter($first_user->toArray()));
    //     $first_user->fill($updated_user)->save();

    //     User::destroy($second_user->_id);

    //     if (isset($second_user->email)) {
    //         echo "user deleted: " . $second_user->email . $second_user->_id . "\n";
    //     } else {
    //         echo "user deleted: " . $second_user->mobile . $second_user->id . "\n";
    //     }
    // }
// }
