<?php

namespace Northstar\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Northstar\Console\Commands\CleanDrupalIdsCommand::class,
        \Northstar\Console\Commands\ConvertMobilesCommand::class,
        \Northstar\Console\Commands\FixE164DuplicatesCommand::class,
        \Northstar\Console\Commands\FixMongoDatesCommand::class,
        \Northstar\Console\Commands\FixSourcesCommand::class,
        \Northstar\Console\Commands\BackfillPhoenixAccounts::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ...
    }
}
