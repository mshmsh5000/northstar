<?php

namespace Northstar\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * Get list of campaigns, or individual campaign information.
     * @see https://github.com/DoSomething/dosomething/wiki/API#campaigns
     *
     * @param int $id - Optional campaign ID to get information on.
     *
     * @return mixed
     */
    public function campaigns($id = null)
    {
        // Get all campaigns if there's no id set.
        if (! $id) {
            $response = $this->client->get('campaigns.json');
        } else {
            $response = $this->client->get('content/'.$id.'.json');
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Forward registration to Drupal.
     * @see: https://github.com/DoSomething/dosomething/wiki/API#create-a-user
     *
     * @param \Northstar\Models\User $user - User to be registered on Drupal site
     *
     * @return int - Created Drupal user UID
     */
    public function register($user)
    {
        $payload = $user->toArray();

        // Format user object for consumption by Drupal API.
        $payload['birthdate'] = format_date($user->birthdate, 'Y-m-d');
        $payload['user_registration_source'] = $user->source;

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
     * Get a user uid by email.
     * @see: https://github.com/DoSomething/dosomething/wiki/API#find-a-user
     *
     * @param string $email - Email of user to search for
     *
     * @return string - Drupal User ID
     * @throws Exception
     */
    public function getUidByEmail($email)
    {
        $response = $this->client->get('users', [
            'query' => [
                'parameters[email]' => $email,
            ],
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        if (count($json) > 0) {
            return $json[0]['uid'];
        } else {
            throw new Exception('Drupal user not found.', $response->getStatusCode());
        }
    }

    /**
     * Get an index of (optionally filtered) campaign signups from Phoenix.
     * @see: https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-signup-collection
     *
     * @param array $query - query string, for filtering results
     * @return array - JSON response
     */
    public function getSignupIndex(array $query = [])
    {
        $response = $this->client->get('signups', [
            'query' => $query,
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get details for a particular campaign signup from Phoenix.
     * @see: https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-signup
     *
     * @return array - JSON response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function getSignup($signup_id)
    {
        try {
            $response = $this->client->get('signups/'.$signup_id, [
                'cookies' => $this->getAuthenticationCookie(),
                'headers' => [
                    'X-CSRF-Token' => $this->getAuthenticationToken(),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundHttpException('That signup could not be found.');
            }

            throw new Exception('Unknown error getting signup: '.$e->getMessage());
        }
    }

    /**
     * Create a new campaign signup on the Drupal site.
     * @see: https://github.com/DoSomething/dosomething/wiki/API#campaign-signup
     *
     * @param string $user_id - UID of user on the Drupal site
     * @param string $campaign_id - NID of campaign on the Drupal site
     * @param string $source - Sign up source (e.g. web, iPhone, etc.)
     *
     * @return string - Signup ID
     * @throws Exception
     */
    public function createSignup($user_id, $campaign_id, $source)
    {
        $payload = [
            'uid' => $user_id,
            'source' => $source,
        ];

        $response = $this->client->post('campaigns/'.$campaign_id.'/signup', [
            'json' => $payload,
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get an index of (optionally filtered) campaign reportbacks from Phoenix.
     * @see: https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-reportback-collection
     *
     * @param array|string $query - query string, for filtering results
     * @return array - JSON response
     */
    public function getReportbackIndex(array $query = [])
    {
        $response = $this->client->get('reportbacks', [
            'query' => $query,
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get details for a particular campaign signup from Phoenix.
     * @see: https://github.com/DoSomething/phoenix/wiki/API#retrieve-a-specific-reportback
     *
     * @return array - JSON response
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function getReportback($reportback_id)
    {
        try {
            $response = $this->client->get('reportbacks/'.$reportback_id, [
                'cookies' => $this->getAuthenticationCookie(),
                'headers' => [
                    'X-CSRF-Token' => $this->getAuthenticationToken(),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new NotFoundHttpException('That reportback could not be found.');
            }

            throw new Exception('Unknown error getting signup: '.$e->getMessage());
        }
    }

    /**
     * Create or update a user's reportback on the Drupal site.
     * @see: https://github.com/DoSomething/dosomething/wiki/API#campaign-reportback
     *
     * @param string $user_id - UID of user on the Drupal site
     * @param string $campaign_id - NID of campaign on the Drupal site
     * @param array $contents - Contents of reportback
     *   @option string $quantity - Quantity of reportback
     *   @option string $why_participated - Why the user participated in this campaign
     *   @option string $file - Reportback image as a Data URL
     *
     * @return array - API response
     * @throws Exception
     */
    public function createReportback($user_id, $campaign_id, $contents)
    {
        $payload = [
            'uid' => $user_id,
            'quantity' => $contents['quantity'],
            'why_participated' => $contents['why_participated'],
            'file' => $contents['file'],
            'filename' => Str::random(10).'.jpg', // Hackz. This sets the filename Phoenix saves reportback with.
            'caption' => $contents['caption'],
            'source' => $contents['source'],
        ];

        $response = $this->client->post('campaigns/'.$campaign_id.'/reportback', [
            'json' => $payload,
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a magic login link for the given user ID.
     * @see: https://github.com/DoSomething/phoenix/blob/dev/documentation/endpoints/users.md#create-magic-login-url
     *
     * @param string $user_id - UID of user on the Drupal site
     *
     * @return array - API response
     * @throws Exception
     */
    public function createMagicLogin($user_id)
    {
        $response = $this->client->post('users/'.$user_id.'/magic_login_url', [
            'cookies' => $this->getAuthenticationCookie(),
            'headers' => [
                'X-CSRF-Token' => $this->getAuthenticationToken(),
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
