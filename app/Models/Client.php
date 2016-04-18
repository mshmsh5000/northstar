<?php

namespace Northstar\Models;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Model;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * The Client model. These identify the "client application" making
 * a request, and their maximum allowed scopes.
 *
 * @property string client_id
 * @property string client_secret
 * @property array $scope
 */
class Client extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'clients';

    /**
     * The model's default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'scope' => [],
    ];

    /**
     * Available API Key scopes.
     * @var array
     */
    protected static $scopes = [
        'admin' => [
            'description' => 'Allows "administrative" actions that should not be user-accessible, like deleting user records.',
        ],
        'user' => [
            'description' => 'Allows actions to be made on a user\'s behalf.',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'scope',

        // For backwards compatibility...
        'app_id',
        'api_key',
    ];

    /**
     * Create a new API key.
     *
     * @param $attributes
     * @return Client
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random API key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        static::creating(function (Client $client) {
            if (empty($client->client_secret)) {
                $client->client_secret = Str::random(32);
            }
        });
    }

    /**
     * Map legacy 'app_id' to it's OAuth equivalent.
     * @return string
     */
    public function setAppIdAttribute($value)
    {
        $this->attributes['client_id'] = snake_case(str_replace(' ', '', $value));
    }

    /**
     * Mutator for 'client_id' attribute.
     * @return string
     */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = snake_case(str_replace(' ', '', $value));
    }

    /**
     * Mutator for 'scope' attribute.
     * @return array
     */
    public function getScopeAttribute()
    {
        if (! isset($this->attributes['scope']) || ! is_array($this->attributes['scope'])) {
            return [];
        }

        return $this->attributes['scope'];
    }

    /**
     * Check if this API key has the given scope.
     *
     * @param $scope - Scope to test for
     * @return bool
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scope);
    }

    /**
     * Validate if all the given scopes are valid.
     *
     * @param $scopes
     * @return bool
     */
    public static function validateScopes($scopes)
    {
        if (! is_array($scopes)) {
            return false;
        }

        return ! array_diff($scopes, array_keys(static::$scopes));
    }

    /**
     * Return a list of all scopes & their descriptions.
     *
     * @return array
     */
    public static function scopes()
    {
        return static::$scopes;
    }

    /**
     * Get the API key specified for the current request.
     *
     * @return \Northstar\Models\ApiKey
     */
    public static function current()
    {
        $client_secret = request()->header('X-DS-REST-API-Key');

        return static::where('client_secret', $client_secret)->first();
    }

    /**
     * Return whether a properly scoped API key is provided
     * with the current request.
     *
     * @param $scope - Required scope
     * @return bool
     */
    public static function allows($scope)
    {
        $key = self::current();

        return $key && $key->hasScope($scope);
    }

    /**
     * Throw an exception if a properly scoped API key is not
     * provided with the current request.
     *
     * @param $scope - Required scope
     */
    public static function gate($scope)
    {
        if (! static::allows($scope)) {
            app('stathat')->ezCount('invalid API key error');
            throw new AccessDeniedHttpException('You must be using an API key with "'.$scope.'" scope to do that.');
        }
    }
}
