<?php

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
     * @test
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
}
