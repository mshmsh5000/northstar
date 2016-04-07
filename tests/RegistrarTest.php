<?php

use Northstar\Auth\Registrar;

class RegistrarTest extends TestCase
{
    /**
     * The registrar to be tested.
     * @var Registrar
     */
    protected $registrar;

    /**
     * Create a new Registrar before each test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->registrar = app(Registrar::class);
    }

    /**
     * Test that we can normalize the ID field name.
     */
    public function testNormalizeId()
    {
        $credentials = [
            'id' => $this->faker->uuid,
        ];

        $normalized = $this->registrar->normalize($credentials);

        $this->assertArrayHasKey('_id', $normalized);
        $this->assertArrayNotHasKey('id', $normalized);
        $this->assertSame($credentials['id'], $normalized['_id']);
    }

    /**
     * Test that we can normalize email addresses.
     */
    public function testNormalizeEmail()
    {
        $normalized = $this->registrar->normalize([
            'email' => 'Kamala.Khan@marvel.com ',
        ]);

        $this->assertSame('kamala.khan@marvel.com', $normalized['email']);
    }

    /**
     * Test that we can normalize mobile phone numbers.
     */
    public function testNormalizeMobile()
    {
        $normalized = $this->registrar->normalize([
            'mobile' => '1 (555) 123-4567',
        ]);

        $this->assertSame('15551234567', $normalized['mobile']);
    }

    /**
     * Test that we can normalize an email provided in the 'username' field.
     */
    public function testNormalizeEmailAsUsername()
    {
        $credentials = [
            'username' => 'Kamala.Khan@marvel.com ',
        ];

        $normalized = $this->registrar->normalize($credentials);

        $this->assertArrayNotHasKey('username', $normalized);
        $this->assertArrayNotHasKey('mobile', $normalized);

        $this->assertSame('kamala.khan@marvel.com', $normalized['email']);
    }

    /**
     * Test that we can normalize an email provided in the 'username' field.
     */
    public function testNormalizeMobileAsUsername()
    {
        $credentials = [
            'username' => '1 (555) 123-4567',
        ];

        $normalized = $this->registrar->normalize($credentials);

        $this->assertArrayNotHasKey('username', $normalized);
        $this->assertArrayNotHasKey('email', $normalized);

        $this->assertSame('15551234567', $normalized['mobile']);
    }

    /**
     * Test that we can normalize multiple fields.
     */
    public function testNormalizeMultipleFields()
    {
        $normalized = $this->registrar->normalize([
            '_id' => $this->faker->uuid,
            'mobile' => $this->faker->phoneNumber,
        ]);

        $this->assertArrayHasKey('_id', $normalized);
        $this->assertArrayHasKey('mobile', $normalized);
        $this->assertArrayNotHasKey('email', $normalized);
    }
}
