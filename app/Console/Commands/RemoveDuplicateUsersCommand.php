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
                        '$match' => [
                                'email' => [
                                    '$ne' => '',
                                ],
                        ],
                    ],
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
        $this->deduplicate($email_duplicates, 'email');

        // Find all duplicate users by mobile.
        $mobile_duplicates = User::raw(function ($collection) {
            return $collection->aggregate(
                [
                    [
                        '$match' => [
                            'mobile' => [
                                '$ne' => '',
                                '$exists' => true,
                            ],
                        ],
                    ],
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
        $this->deduplicate($mobile_duplicates, 'mobile');
        $this->info('Script successful. Re-run script until no users are deleted and yields only this message. If only this message, deduplication complete!');
    }

    /**
     * Combine all user info. into one record and delete all duplicates.
     * @param  array $duplicates
     * @param  string $type - describes if it is an email or mobile duplicate
     * @return void
     */
    public function deduplicate($duplicates, $type)
    {
        foreach ($duplicates['result'] as $user) {
            $length = $user['count'];

            // No need to deduplicate anything
            if ($length <= 1) {
                continue;
            }

            for ($i = 0; $i < $length - 1; $i++) {
                // Default master doc is the first one in the list (just to give us somewhere to start)
                $master_doc = User::where('_id', '=', $user['uniqueIds'][$i]->{'$id'})->first();

                if (! $master_doc) {
                    echo "ERROR can't find a doc for: ".$user['uniqueIds'][$i]->{'$id'}."\n";
                    continue;
                }

                // Ensure the doc actually has value that matches the $type we're running this for.
                if (($type == 'mobile' && empty($master_doc['mobile'])) ||
                    ($type == 'email' && empty($master_doc['email']))) {
                    continue;
                }

                $master_doc_arr = $master_doc->toArray();

                $compare_doc = User::where('_id', '=', $user['uniqueIds'][$i + 1]->{'$id'})->first();
                $compare_doc_arr = $compare_doc->toArray();

                // Convert string to date and ensure that master_doc is the original user created.
                $master_doc_created_at = strtotime($master_doc_arr['created_at']) * 1000;
                $compare_doc_created_at = strtotime($compare_doc_arr['created_at']) * 1000;

                if ($master_doc_created_at > $compare_doc_created_at) {
                    $tmp_arr = $compare_doc_arr;
                    $compare_doc_arr = $master_doc_arr;
                    $master_doc_arr = $tmp_arr;

                    $tmp = $compare_doc;
                    $compare_doc = $master_doc;
                    $master_doc = $tmp;
                }

                foreach ($compare_doc_arr as $key => $value) {
                    $updated_user = [];
                    if (is_string($value)) {
                        if (empty($master_doc_arr[$key])) {
                            $master_doc_arr[$key] = $value;
                        }
                    } elseif (is_array($value)) {
                        if (isset($compare_doc_arr[$key])) {
                            if (! in_array($key, $master_doc_arr)) {
                                $updated_user[$key] = $compare_doc_arr[$key];
                                $master_doc->fill($updated_user)->save();
                            } else {
                                $master_doc_arry[$key] = array_push($master_doc_arr[$key], $compare_doc_arr[$key]);
                            }
                        }
                    }
                }

                // Fill model with updates. array_filter to remove any keys with null values.
                $master_doc->fill(array_filter($master_doc_arr));
                $master_doc->save();

                // Delete the compare_doc
                User::destroy($compare_doc_arr['_id']);
                if (! empty($compare_doc_arr['email'])) {
                    echo 'user deleted: '.$compare_doc_arr['email'].' '.$compare_doc_arr['_id']."\n";
                } elseif (! empty($compare_doc_arr['mobile'])) {
                    echo 'user deleted: '.$compare_doc_arr['mobile'].' '.$compare_doc_arr['_id']."\n";
                }
            }
        }
    }
}
