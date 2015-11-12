<?php namespace Northstar\Services;

use Northstar\Models\User;
use Hash;

class DrupalPasswordChecker
{
    public function __construct(Password $password)
    {
        $this->password = $password;
    }

    public function user_check_password($password, $drupal_password)
    {
        if (substr($drupal_password, 0, 2) == 'U$') {
            // This may be an updated password from user_update_7000(). Such hashes
            // have 'U' added as the first character and need an extra md5().
            $stored_hash = substr($drupal_password, 1);
            $password = md5($password);
        }
        else {
            $stored_hash = $drupal_password;
        }

        $type = substr($stored_hash, 0, 3);
        switch ($type) {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $hash = $this->password->_password_crypt('sha512', $password, $stored_hash);
                break;
            case '$H$':
                // phpBB3 uses "$H$" for the same thing as "$P$".
            case '$P$':
                // A phpass password generated using md5.  This is an
                // imported password or from an earlier Drupal version.
                $hash = _password_crypt('md5', $password, $stored_hash);
                break;
                default:
                return FALSE;
            }
            return ($hash && $stored_hash == $hash);
    }
}
