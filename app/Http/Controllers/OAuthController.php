<?php

namespace Northstar\Http\Controllers;

use League\OAuth2\Server\Exception\OAuthServerException;
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
     * Authenticate a registered user based on the given credentials,
     * and return an authentication token.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Illuminate\Http\Response
     */
    public function createToken(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            return $this->oauth->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            // @TODO: Move to Handler.php!
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * Logout the current user by invalidating their session token.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Illuminate\Http\Response
     */
    public function invalidateToken(ServerRequestInterface $request, ResponseInterface $response)
    {
        // ...
    }
}
