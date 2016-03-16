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
        'password' => $faker->password,
        'birthdate' => $faker->date($format = 'm/d/Y', $max = 'now'),
        'addr_street1' => $faker->streetAddress,
        'city' => $faker->city,
        'addr_state' => $faker->stateAbbr,
        'addr_zip' => $faker->postcode,
        'country' => $faker->countryCode,
        'language' => $faker->languageCode,
        'drupal_id' => $faker->numberBetween(1, 400000),
        'source' => $faker->randomElement(['phoenix', 'cgg', 'agg', 'sms']),
    ];
});
