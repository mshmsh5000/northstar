<?php

namespace Northstar\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\Server as OAuthServer;

class OAuthController extends Controller
{
    /**
     * The OAuth authorization server.
     * @var OAuthServer
     */
    protected $oauth;

    /**
     * AuthController constructor.
     * @param OAuthServer $oauth
     */
    public function __construct(OAuthServer $oauth)
    {
        $this->oauth = $oauth;
    }

    /**
     * Authenticate a registered user using one of the supported OAuth
     * grants and return token details.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Illuminate\Http\Response
     */
    public function createToken(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $this->oauth->respondToAccessTokenRequest($request, $response);
    }

    /**
     * Logout the current user by invalidating their refresh token.
     *
     * @return \Illuminate\Http\Response
     */
    public function invalidateToken()
    {
        // @TODO: Implement this!
        return response('Not yet implemented.', 501);
    }
}
