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
            'app_id' => '456',
            'api_key' => 'abc4324',
            'scope' => ['admin', 'user'],
        ]);

        Client::create([
            'app_id' => '123',
            'api_key' => '5464utyrs',
            'scope' => ['user'],
        ]);
    }
}
