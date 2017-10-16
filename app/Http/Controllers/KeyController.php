<?php

namespace Northstar\Http\Controllers;

use JOSE_JWK;
use phpseclib\Crypt\RSA;

class KeyController extends Controller
{
    /**
     * Make a new KeyController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('role:admin', ['only' => 'show']);
    }

    /**
     * Return the public key formatted as a JWK, which can be
     * used by other resource servers to verify JWTs.
     *
     * @return array
     */
    public function index()
    {
        $path = base_path('storage/keys/public.key');
        $key = new RSA();

        // Create the JWK payload for our public key.
        $key->loadKey(file_get_contents($path));
        $jwk = json_decode((string) JOSE_JWK::encode($key));

        return [
            'keys' => [$jwk],
        ];
    }

    /**
     * Return the public key, which can be used by other services
     * to verify JWT access tokens.
     * GET /key
     *
     * @return array
     */
    public function show()
    {
        $path = base_path('storage/keys/public.key');
        $publicKey = file_get_contents($path);

        return [
            'algorithm' => 'RS256',
            'issuer' => url('/'),
            'public_key' => $publicKey,
        ];
    }
}
