<?php

use Illuminate\Database\Seeder;
use Northstar\Models\Client;

class ApiKeyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('client')->delete();

        Client::create([
            'client_id' => '456',
            'client_secret' => 'abc4324',
            'scope' => ['admin', 'user'],
        ]);

        Client::create([
            'client_id' => '123',
            'client_secret' => '5464utyrs',
            'scope' => ['user'],
        ]);
    }
}
