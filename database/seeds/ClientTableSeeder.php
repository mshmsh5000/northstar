<?php

use Illuminate\Database\Seeder;
use Northstar\Models\Client;
use Northstar\Auth\Scope;

class ApiKeyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->delete();

        // For easy testing, we'll seed one client with all scopes...
        Client::create([
            'client_id' => 'trusted-test-client',
            'client_secret' => 'secret1',
            'scope' => collect(Scope::all())->keys()->toArray(),
        ]);

        // ..and one with limited scopes.
        Client::create([
            'client_id' => 'untrusted-test-client',
            'client_secret' => 'secret2',
            'scope' => ['user'],
        ]);
    }
}
