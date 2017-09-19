<?php

namespace Northstar\Auth;

use Carbon\Carbon;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

class Normalizer
{
    /**
     * Normalize the given credentials in the array or request (for example, before
     * validating, or before saving to the database).
     *
     * @param \ArrayAccess|array $credentials
     * @return mixed
     */
    public function credentials($credentials)
    {
        // If a username is given, figure out whether it's an email or mobile number.
        if (! empty($credentials['username'])) {
            $type = $this->isEmail($credentials['username']) ? 'email' : 'mobile';
            $credentials[$type] = $credentials['username'];
            unset($credentials['username']);
        }

        // Map id to Mongo's _id ObjectID field.
        if (! empty($credentials['id'])) {
            $credentials['_id'] = $credentials['id'];
            unset($credentials['id']);
        }

        if (! empty($credentials['email'])) {
            $credentials['email'] = $this->email($credentials['email']);
        }

        if (! empty($credentials['mobile'])) {
            $credentials ['mobile'] = $this->mobile($credentials['mobile']);
        }

        return $credentials;
    }

    /**
     * Sanitize an email address before verifying or saving to the database.
     * This method will likely be called multiple times per user, so it *must*
     * provide the same result if so.
     *
     * @param string $email
     * @return string
     */
    public function email($email)
    {
        return trim(strtolower($email));
    }

    /**
     * Sanitize a mobile number before verifying or saving to the database.
     * This method will likely be called multiple times per user, so it *must*
     * provide the same result if so.
     *
     * @param string $mobile
     * @return string
     */
    public function mobile($mobile)
    {
        if (empty($mobile)) {
            return null;
        }

        try {
            $parser = PhoneNumberUtil::getInstance();
            $number = $parser->parse($mobile, 'US');

            return $parser->format($number, PhoneNumberFormat::E164);
        } catch (\libphonenumber\NumberParseException $e) {
            return null;
        }
    }

    /**
     * Parse a string into a Carbon date.
     *
     * @param string[] $strings
     * @return Carbon[]
     */
    public function dates($strings)
    {
        $dates = collect($strings)->map(function ($string) {
            return new Carbon($string);
        });

        return $dates->toArray();
    }

    /**
     * Confirm that the given value is an e-mail address.
     *
     * @param string $value
     * @return bool
     */
    protected function isEmail($value)
    {
        return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
    }
}
