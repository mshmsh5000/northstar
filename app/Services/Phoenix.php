<?php

namespace Northstar\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Cache;

class Phoenix
{
    protected $client;

    public function __construct()
    {
        $base_url = config('services.drupal.url');
        $version = config('services.drupal.version');

        $this->client = new Client([
            'base_uri' => $base_url.'/api/'.$version.'/',
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }

    /**
     * Returns a token for making authenticated requests to the Drupal API.
     *
     * @return array - Cookie & token for authenticated requests
     */
    private function authenticate()
    {
        $authentication = Cache::remember('drupal.authentication', 30, function () {
            $payload = [
                'username' => config('services.drupal.username'),
                'password' => config('services.drupal.password'),
            ];

            $response = $this->client->post('auth/login', [
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            $session_name = $body['session_name'];
            $session_value = $body['sessid'];

            return [
                'cookie' => [$session_name => $session_value],
                'token' => $body['token'],
            ];
        });

        return $authentication;
    }

    /**
     * Get the CSRF token for the authenticated API session.
     *
     * @return string - token
     */
    private function getAuthenticationToken()
    {
        return $this->authenticate()['token'];
    }

    /**
     * Get the cookie for the authenticated API session.
     *
     * @return CookieJar
     */
    private function getAuthenticationCookie()
    {
        $cookieDomain = parse_url(config('services.drupal.url'))['host'];

        return CookieJar::fromArray($this->authenticate()['cookie'], $cookieDomain);
    }

    /**
     * Forward registration to Drupal.
     * @see: https://github.com/DoSomething/dosomething/wiki/API#create-a-user
     *
     * @param \Northstar\Models\User $user - User to be registered on Drupal site
     *
     * @return int - Created Drupal user UID
     */
    public function createDrupalUser($user)
    {
        $payload = $user->toArray();

        // Format user object for consumption by Drupal API.
        $payload['birthdate'] = format_date($user->birthdate, 'Y-m-d');
        $payload['user_registration_source'] = $user->source;

        // Drupal requires an email on every account, but Northstar does not.
        if (empty($payload['email'])) {
            $payload['email'] = $user->id.'@dosomething.import';
        }

        $response = $this->client->post('users', [
            'query' => [
                'forward' => false,
            ],
            'json' => $payload,
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        return $json['uid'];
    }

    /**
     * Trigger a transactional message.
     *
     * @param string $id
     * @param string $template
     * @return void
     */
    public function sendTransactional($id, $template)
    {
        $this->client->post('transactionals', [
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
            'json' => [
                'id' => $id,
                'template' => $template,
            ],
        ]);
    }
}
