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
        // @TODO: Why is this being called... it's called for each unit test.
        // Without this line, the unit tests fail.
        DB::table('users')->delete();

        // Non-signed up user
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
        ]);

        // Signed up user
        User::create([
            '_id' => '5480c950bffebc651c8b456f',
            'email' => 'test1@dosomething.org',
            'mobile' => '5555550101',
            'password' => 'secret',
            'drupal_id' => '100002',
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
            'campaigns' => [
                [
                    '_id' => '5480c950bffebc651c8b456e',
                    'drupal_id' => '123',
                    'signup_id' => '100',
                    'signup_source' => 'android',
                ]
            ]
        ]);

        // Reported back user
        User::create([
            '_id' => 'bf1039b0271bcc636aa5477a',
            'email' => 'test2@dosomething.org',
            'mobile' => '5555550102',
            'password' => 'secret',
            'drupal_id' => '100003',
            'addr_street1' => '123',
            'addr_street2' => '456',
            'addr_city' => 'Paris',
            'addr_state' => 'Florida',
            'addr_zip' => '555555',
            'country' => 'US',
            'birthdate' => '12/17/91',
            'first_name' => 'First',
            'last_name' => 'Last',
            'campaigns' => [
                [
                    '_id' => '3f10c910251bcc636aa5477a',
                    'drupal_id' => '123',
                    'signup_id' => '101',
                    'signup_source' => 'ios',
                    'reportback_id' => '125'
                ]
            ]
        ]);

        // User invited to campaign
        User::create([
            '_id' => 'bf1039b0271bcc636aa5477b',
            'email' => 'test3@dosomething.org',
            'mobile' => '5555550102',
            'password' => 'secret',
            'drupal_id' => '100004',
            'birthdate' => '12/17/91',
            'campaigns' => [
                [
                    '_id' => '3f10c910251bcc636aa5477b',
                    'drupal_id' => '123',
                    'signup_id' => '102',
                    'signup_source' => 'test',
                    'signup_group' => '100',
                ]
            ]
        ]);

        // Parse user to be logged out
        User::create(array(
            '_id' => 'bf1039b0271bcc636aa5477c',
            'parse_installation_ids' => 'parse-abc123'
        ));

        // User 1 for push notification tests
        User::create(array(
            '_id' => 'bf1039b0271bcc636aa5477d',
            'drupal_id' => '100005',
            'parse_installation_ids' => 'parse-100',
            'campaigns' => [
                [
                    'drupal_id' => '123',
                    'signup_id' => '200',
                    'signup_source' => 'test',
                    'signup_group' => '200',
                ]
            ]
        ));

        // User 2 for push notification tests
        User::create(array(
            '_id' => 'bf1039b0271bcc636aa5477e',
            'drupal_id' => '100006',
            'first_name' => 'Push',
            'last_name' => 'User',
            'parse_installation_ids' => 'parse-101',
            'campaigns' => [
                [
                    'drupal_id' => '123',
                    'signup_id' => '201',
                    'signup_source' => 'test',
                    'signup_group' => '200',
                    'reportback_id' => '1000',
                ]
            ]
        ));

        User::create(array(
            'email' => 'info@dosomething.org',
            'mobile' => '5555550104',
            'password' => 'secret',
            'drupal_id' => '456788',
            'addr_street1' => '456',
            'addr_street2' => '33',
            'addr_city' => 'Example',
            'addr_state' => 'Testing',
            'addr_zip' => '555555',
            'country' => 'US',
            'birthdate' => '12/17/91',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ));

        User::create(array(
            '_id' => '5480c950bffebc651c8b4570',
            'email' => 'delete-test@ds.org',
            'password' => 'secret'
        ));

        if (App::environment('local')) {
            $faker = Faker\Factory::create();
            foreach (range(1, 1000) as $index) {
                User::create([
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email' => $faker->unique()->safeEmail,
                    'mobile' => $faker->unique()->phoneNumber,
                    'password' => 'secret',
                    'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
                    'addr_street1' => $faker->streetAddress,
                    'addr_street2' => $faker->secondaryAddress,
                    'city' => $faker->city,
                    'addr_state' => $faker->state,
                    'addr_zip' => $faker->postcode,
                    'country' => $faker->country,
                    'cgg_id' => $index,
                    'source' => $faker->randomElement(['cgg', 'drupal', 'agg', 'services']),
                ]);
            }
        }
    }

}
