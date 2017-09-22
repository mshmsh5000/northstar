<?php

namespace Northstar\Console\Commands;

use Illuminate\Support\Collection;
use Northstar\Models\User;
use Illuminate\Console\Command;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class ConvertMobilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:e164 {start?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert mobile numbers to E.164 format.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $start = $this->argument('start');
        if (! $start) {
            $firstMatch = User::whereNull('e164')->whereNotNull('mobile')->orderBy('_id')->first();
            if (! $firstMatch) {
                $this->error('No users need to be converted.');
                return;
            }

            $start = $firstMatch->id;
        }

        $counter = 1;

        // Iterate over users where the `mobile` field is not null.
        User::whereNull('e164')->whereNotNull('mobile')->chunkFromId(200, $start, function (Collection $records) use (&$counter, $start) {
            $parser = PhoneNumberUtil::getInstance();
            $users = User::hydrate($records->toArray());

            /** @var User $user */
            foreach ($users as $user) {
                $this->line('['.$counter.'] '.$user->id.' - '.$user->mobile);

                try {
                    // Parse & format as E.164.
                    $number = $parser->parse($user->mobile, 'US');
                    $formattedNumber = $parser->format($number, PhoneNumberFormat::E164);

                    // Save to the `e164` field. We'll swap this with `mobile` later.
                    $user->e164 = $formattedNumber;
                    $user->save(['touch' => false]);

                    $this->info('['.$counter.'] '.$user->id.' - Formatted as '.$formattedNumber);
                } catch (\libphonenumber\NumberParseException $e) {
                    $this->error('['.$counter.'] '.$user->id.' - Could not parse.');

                    // If they don't have one, give this user an email so we can safely drop `mobile`.
                    if (empty($user->email)) {
                        $user->email = 'invalid-mobile-'.$user->id.'@dosomething.invalid';
                        $user->save();
                    }
                }

                $counter++;
            }
        }, '_id');
    }
}
