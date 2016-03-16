<?php

use Illuminate\Database\Seeder;
use Northstar\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear the database.
        DB::table('users')->delete();

        // Create a user with a predetermined ID to use when manually testing endpoints.
        User::create([
            '_id' => '5430e850dt8hbc541c37tt3d',
            'email' => 'test@dosomething.org',
            'mobile' => '5555550100',
            'password' => 'secret',
            'drupal_id' => '100001',
            'addr_street1' => '123',
            'addr_street2' => '456',
            'addr_city' => 'Paris',
            'addr_state' => 'Florida',
            'addr_zip' => '555555',
            'country' => 'US',
            'birthdate' => '12/17/91',
            'first_name' => 'First',
            'last_name' => 'Last',
            'parse_installation_ids' => 'parse-abc123',
        ]);

        // Then create a bunch of randomly-generated test data!
        factory(User::class, 250)->create();
    }
}
