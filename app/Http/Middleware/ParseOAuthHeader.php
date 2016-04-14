<?php

namespace Northstar\Http\Middleware;

use League\OAuth2\Server\Server as OAuthServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ParseOAuthHeader
{
    /**
     * Authenticate constructor.
     * @param OAuthServer $oauth
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(OAuthServer $oauth, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->oauth = $oauth;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $header = $request->header('Authorization');

        // If the 'Authorization' header is set, and it's not the legacy
        // 32 character key (with the "Bearer " prefix = 39 characters)...
        if (! empty($header) && strlen($header) !== 39) {
            $this->request = $this->oauth->validateAuthenticatedRequest($this->request);

            // Add the parsed attributes (oauth_access_token_id, oauth_client_id,
            // oauth_user_id, & oauth_scopes) to the request.
            $request->attributes->add($this->request->getAttributes());
        }

        return $next($request);
    }
}
