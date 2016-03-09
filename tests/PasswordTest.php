<?php

use Northstar\Auth\DrupalPasswordHash;
use Northstar\Models\User;

class PasswordTest extends TestCase
{
    /**
     * Tests that Drupal password hasher is working correctly.
     */
    public function testAuthenticatingWithDrupalPassword()
    {
        $user = User::create([
            'email' => 'dries.buytaert@example.com',
            'drupal_password' => '$S$DOQoztwlGzTeaobeBZKNzlDttbZscuCkkZPv8yeoEvrn26H/GN5b',
        ]);

        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);

        // Assert response is 200 OK and has expected data
        $this->assertResponseStatus(200);
        $this->seeJsonSubset([
            'data' => [
                'id' => $user->_id,
                'email' => $user->email,
            ],
        ]);

        // Assert user has been updated in the database with a newly hashed password.
        $user = $user->fresh();
        $this->assertArrayNotHasKey('drupal_password', $user['attributes']);
        $this->assertArrayHasKey('password', $user['attributes']);

        // Finally, let's try logging in with the newly hashed password
        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'dries.buytaert@example.com',
            'password' => 'secret',
        ]);
        $this->assertResponseStatus(200);
    }

    /**
     * Test that updating a user's password will re-hash it.
     */
    public function testUpdatingUser()
    {
        $user = User::create([
            'email' => 'acquia.consultant@example.com',
            'drupal_password' => '$S$DOQoztwlGzTeaobeBZKNzlDttbZscuCkkZPv8yeoEvrn26H/GN5b',
        ]);

        $this->withScopes(['admin'])->json('PUT', 'v1/users/_id/'.$user->_id, [
            'password' => 'secret'
        ]);

        $this->assertResponseStatus(200);

        // Assert user has been updated in the database with a newly hashed password.
        $user = $user->fresh();
        $this->assertArrayNotHasKey('drupal_password', $user['attributes']);
        $this->assertArrayHasKey('password', $user['attributes']);

        // Finally, let's try logging in with the newly hashed password
        $this->withScopes(['user'])->json('POST', 'v1/auth/verify', [
            'email' => 'acquia.consultant@example.com',
            'password' => 'secret',
        ]);
        $this->assertResponseStatus(200);
    }

    /**
     * Test the Drupal password hasher against known good/bad
     * password hashes.
     */
    public function testHashesCorrectly()
    {
        // Succeeds if given a good password
        $this->assertTrue(DrupalPasswordHash::check('testtest', '$S$DYvEbMTfOWVPq5FyHhp70eXBrt8FClzE8bV8RoR8alahwR71PoLE'), 'Succeeds if given a good password');

        // Fails if given a bad password
        $this->assertFalse(DrupalPasswordHash::check('secret', '$S$DYvEbMTfOWVPq5FyHhp70eXBrt8FClzE8bV8RoR8alahwR71PoLE'), 'Fails if given a bad password');

        // Can check older MD5 passwords.
        $this->assertTrue(DrupalPasswordHash::check('derpalicious', '$P$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91'), 'Password check succeeds on valid MD5 password.');
        $this->assertTrue(DrupalPasswordHash::check('derpalicious', '$H$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91'), 'Password check succeeds on valid MD5 password.');
        $this->assertFalse(DrupalPasswordHash::check('nowaytraderjose', '$P$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91'), 'Password check fails on invalid MD5 password.');
        $this->assertFalse(DrupalPasswordHash::check('nowaytraderjose', '$H$DxTIL/YfZCdJtFYNh1Ef9ERbMBkuQ91'), 'Password check fails on invalid MD5 password.');
    }
}
