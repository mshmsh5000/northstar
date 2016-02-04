<?php

namespace Northstar\Models;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Model;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiKey extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'api_keys';

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
        'admin' => 'Allows "administrative" actions that should not be user-accessible, like deleting user records.',
        'user' => 'Allows actions to be made on a user\'s behalf.',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id',
        'scope',
    ];

    /**
     * Create a new API key.
     *
     * @param $attributes
     * @return ApiKey
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random API key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        static::creating(function (ApiKey $apiKey) {
            if (empty($apiKey->api_key)) {
                do {
                    $key = Str::random(32);
                } while (static::where('api_key', $key)->exists());

                $apiKey->api_key = $key;
            }
        });
    }

    /**
     * Mutator for 'app_id' attribute.
     * @return string
     */
    public function setAppIdAttribute($app_id)
    {
        $this->attributes['app_id'] = snake_case(str_replace(' ', '', $app_id));
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
        $api_key = request()->header('X-DS-REST-API-Key');

        return static::where('api_key', $api_key)->first();
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
            throw new AccessDeniedHttpException('You must be using an API key with "'.$scope.'" scope to do that.');
        }
    }
}
