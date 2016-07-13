<?php

namespace Northstar\Http\Controllers;

class KeyController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Return the public key, which can be used by other services
     * to verify JWT access tokens.
     * GET /key
     *
     * @return \Illuminate\Http\Response
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
