<?php namespace Northstar\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Northstar\Models\User;
use Config;

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

        // Get all users and sort alphabetically.

        // if (isset($last_checked)) {
            // $users_alphabetical_order = User::where('email', 'ASC') > $last_checked;
        // } else {
            $users_alphabetical_order = User::orderBy('email', 'ASC')->get();

        // Go through alphabetized array and compare each record to look for duplicates.
        $length = count($users_alphabetical_order);

        for ($i = 0; $i < $length-1; $i++) {
            if ($users_alphabetical_order[$i]->email == $users_alphabetical_order[$i + 1]->email) {
                $this->combine($users_alphabetical_order[$i], $users_alphabetical_order[$i + 1]);
            }
        }
        $this->info('Deduplication complete.');
        // $this->info(User::orderBy('email', 'ASC')->pluck('email')->toArray());
    }

    /**
     * Combine fields with information from first created user and delete duplicate records.
     */
    public function combine($first_user, $second_user)
    {
        // Always make sure $first_user is the "original" user that we're going merge.
        if ($first_user->created_at > $second_user->created_at) {
            $tmp = $second_user;
            $second_user = $first_user;
            $first_user = $tmp;
        }

        // Merge their data and save to the first user
        $updated_user = array_merge(array_filter($second_user->toArray()), array_filter($first_user->toArray()));
        $first_user->fill($updated_user)->save();


        // $second_user->delete();
        User::destroy($second_user->_id);
        echo "user deleted: " . $second_user->email . $second_user->_id . "\n";
    }
}
