<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Northstar\Models\User;

class FixSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:sources {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect sources using the provided CSV.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //
        $this->info('Done!');
    }
