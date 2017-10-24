<?php

namespace Northstar\Console\Commands;

use phpseclib\Crypt\RSA;
use Illuminate\Console\Command;

class KeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:keys {--force : Overwrite any keys that already exist.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create public & private key for signing tokens.';

    /**
     * Execute the console command.
     *
     * @param RSA $rsa
     * @return void
     */
    public function handle(RSA $rsa)
    {
        // Shamelessly borrowed from Laravel Passport! (https://git.io/vdj4A)
        $keys = $rsa->createKey(4096);

        list($publicKey, $privateKey) = [
            storage_path('keys/public.key'),
            storage_path('keys/private.key'),
        ];

        if ((file_exists($publicKey) || file_exists($privateKey)) && ! $this->option('force')) {
            $this->error('Encryption keys already exist. Use the --force option to overwrite them.');

            return;
        }

        file_put_contents($publicKey, array_get($keys, 'publickey'));
        file_put_contents($privateKey, array_get($keys, 'privatekey'));

        $this->info('Encryption keys generated successfully.');
    }
}
