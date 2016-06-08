<?php

namespace Northstar\Auth;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\CryptTrait;

class Encrypter
{
    use CryptTrait;

    public function __construct()
    {
        $publicKey = base_path('storage/keys/public.key');
        $this->setPublicKey(new CryptKey($publicKey));
    }

    public function decryptData($encryptedData)
    {
        return json_decode($this->decrypt($encryptedData), true);
    }
}
