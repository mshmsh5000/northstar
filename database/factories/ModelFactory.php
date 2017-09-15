<?php

/**
 * Define all of our model factories. Model factories give
 * you a convenient way to create models for testing and seeding your
 * database. Just tell the factory how a default model should look.
 *
 * @var \Illuminate\Database\Eloquent\Factory $factory
 */
$factory->define(Northstar\Models\User::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->optional(0.5)->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'mobilecommons_id' => $faker->randomNumber(5),
        'sms_status' => $faker->randomElement(['active', 'undeliverable']),
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'addr_street1' => $faker->streetAddress,
        'city' => $faker->city,
        'addr_state' => $faker->stateAbbr,
        'addr_zip' => $faker->postcode,
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
    ];
});

$factory->defineAs(Northstar\Models\User::class, 'staff', function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'staff',
    ];
});

$factory->defineAs(Northstar\Models\User::class, 'admin', function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'mobile' => $faker->unique()->phoneNumber,
        'facebook_id' => $faker->unique()->randomNumber(),
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'source' => 'factory',
        'role' => 'admin',
    ];
});
