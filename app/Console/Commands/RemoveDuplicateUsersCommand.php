<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Northstar\Models\User;

class RemoveDuplicateUsersCommand extends Command
{
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
        $email_duplicates = User::raw(function ($collection) {
            return $collection->aggregate(
                [
                    [
                        '$group' => [
                            '_id' => [
                                'email' => '$email',
                            ],
                            'uniqueIds' => [
                                '$addToSet' => '$_id',
                            ],
                            'count' => [
                                '$sum' => 1,
                            ],
                        ],
                    ],
                    [
                        '$match' => [
                            'count' => [
                                '$gt' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'allowDiskUse' => true,
                ]
            );
        });

        // Delete email duplicates.
        $this->deduplicate($email_duplicates);

        // Find all duplicate users by mobile.
        $mobile_duplicates = User::raw(function ($collection) {
            return $collection->aggregate(
                [
                    [
                        '$group' => [
                            '_id' => [
                                'mobile' => '$mobile',
                            ],
                            'uniqueIds' => [
                                '$addToSet' => '$_id',
                            ],
                            'count' => [
                                '$sum' => 1,
                            ],
                        ],
                    ],
                    [
                        '$match' => [
                            'count' => [
                                '$gt' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'allowDiskUse' => true,
                ]
            );
        });

        // Delete mobile duplicates.
        $this->deduplicate($mobile_duplicates);

        $this->info('Deduplication complete.');
    }

    /**
     * Combine all user info. into one record and delete all duplicates.
     * @param  array $duplicates
     * @return void
     */
    public function deduplicate($duplicates)
    {
        foreach ($duplicates['result'] as $user) {
            $length = $user['count'];

            for ($i = 0; $i < $length - 1; $i++) {
                if (count($user['uniqueIds']) > 1) {
                    $duplicate_id = $user['uniqueIds'][$i]->{'$id'};
                    $second_user = User::where('_id', '=', $duplicate_id)->first();
                    $first_user = User::where('_id', '=', $user['uniqueIds'][$i + 1]->{'$id'})->first();

                    $first_user_array = array_filter($first_user->toArray());
                    $second_user_array = array_filter($second_user->toArray());

                    foreach ($second_user_array as $key => $value) {
                        $updated_user = [];
                        if (is_string($value)) {
                            if (! isset($first_user_array[$key]) && (isset($second_user_array[$key]))) {
                                $updated_user[$key] = $second_user_array[$key];
                                $first_user->fill($updated_user)->save();
                            } else {
                                $updated_user[$value] = $first_user_array[$key];
                                $first_user->fill($updated_user)->save();
                            }
                        } elseif (is_array($value)) {
                            if (isset($second_user_array[$key])) {
                                if (! in_array($key, $first_user_array)) {
                                    $updated_user[$key] = $second_user_array[$key][0];
                                    $first_user->fill($updated_user)->save();
                                } else {
                                    array_push($first_user_array[$key], $second_user_array[$key][0]);
                                }
                            }
                        }
                    }

                    User::destroy($duplicate_id);

                    if (isset($user['_id']['email'])) {
                        echo 'user deleted: '.$user['_id']['email'].' '.$duplicate_id."\n";
                    } elseif (isset($user['_id']['mobile'])) {
                        echo 'user deleted: '.$user['_id']['mobile'].' '.$duplicate_id."\n";
                    }
                }
            }
        }
    }
}
