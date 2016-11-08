<?php

namespace Northstar\Http\Controllers;

class KeyController extends Controller
{
    /**
     * Make a new KeyController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('role:admin');
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
