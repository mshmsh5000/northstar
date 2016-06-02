<?php

namespace Northstar\Http\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;

class OAuthController extends Controller
{
    /**
     * The OAuth authorization server.
     * @var AuthorizationServer
     */
    protected $oauth;

    /**
     * Make a new OAuthController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param AuthorizationServer $oauth
     */
    public function __construct(AuthorizationServer $oauth)
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
