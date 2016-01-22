<?php

namespace Northstar\Models;

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
        'scope' => ['user'],
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
        if (empty($this->api_key)) {
            $this->api_key = str_random(40);
        }
    }

    /**
     * Mutator for 'app_id' field.
     */
    public function setAppIdAttribute($app_id)
    {
        $this->attributes['app_id'] = snake_case(str_replace(' ', '', $app_id));
    }

    /**
     * Getter for 'scope' field.
     */
    public function getScopeAttribute()
    {
        if (empty($this->attributes['scope'])) {
            return ['user'];
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
        $app_id = request()->header('X-DS-Application-Id');
        $api_key = request()->header('X-DS-REST-API-Key');

        return static::where('app_id', $app_id)->where('api_key', $api_key)->first();
    }

    /**
     * Throw an exception if a properly scoped API key is not
     * provided with the current request.
     *
     * @param $scope
     */
    public static function gate($scope)
    {
        $key = self::current();

        if (! $key || ! $key->hasScope($scope)) {
            throw new AccessDeniedHttpException('You must be using an API key with "'.$scope.'" scope to do that.');
        }
    }
}
