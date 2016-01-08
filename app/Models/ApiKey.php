<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

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
     * Validate if all the given scopes are valid.
     *
     * @param $scopes
     * @return boolean
     */
    public static function validateScopes($scopes)
    {
        if(! is_array($scopes)) return false;

        return !array_diff($scopes, array_keys(static::$scopes));
    }

    /**
     * Return a list of all scopes & their descriptions.
     * @return array
     */
    public static function scopes()
    {
        return static::$scopes;
    }
}
