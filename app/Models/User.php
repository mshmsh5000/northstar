<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Request;
use Hash;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    protected $fillable = [
        'email', 'mobile', 'password', 'drupal_password',

        'first_name', 'last_name', 'birthdate', 'photo', 'interests',
        'race', 'religion',

        'school_id', 'college_name', 'degree_type', 'major_name', 'hs_gradyear', 'hs_name',
        'sat_math', 'sat_verbal', 'sat_writing',

        'addr_street1', 'addr_street2', 'addr_city', 'addr_state', 'addr_zip', 'country',

        'cgg_id', 'drupal_id', 'agg_id', 'source',

        'parse_installation_ids',
    ];

    /**
     * Attributes that can be queried as unique identifiers.
     *
     * This array is manually maintained. It does not necessarily mean that
     * they are actual indexes on the database.
     *
     * @var array
     */
    public static $indexes = [
        '_id', 'drupal_id', 'email', 'mobile',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['drupal_password', 'password'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cgg_id' => 'integer',
    ];

    /**
     * The attributes which should be stored as MongoDate objects.
     * @see https://github.com/jenssegers/laravel-mongodb#dates
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Email address mutator that converts the email value to lowercase.
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Computed last initial field, for public profiles.
     * @return string
     */
    public function getLastInitialAttribute()
    {
        $initial = substr($this->last_name, 0, 1);

        return strtoupper($initial);
    }

    /**
     * Interests mutator converting comma-delimited string to an array.
     */
    public function setInterestsAttribute($value)
    {
        $interests = is_array($value) ? $value : array_map('trim', explode(',', $value));

        $this->push('interests', $interests, true);
    }

    /**
     * Mobile number mutator that converts number value to only numbers.
     */
    public function setMobileAttribute($value)
    {
        // Skip mutator if attribute is null.
        if (empty($value)) {
            return;
        }

        // Otherwise, remove all non-numeric characters.
        $this->attributes['mobile'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator saves Parse installation ids as an array.
     */
    public function setParseInstallationIdsAttribute($value)
    {
        $ids = is_array($value) ? $value : array_map('trim', explode(',', $value));

        $this->push('parse_installation_ids', $ids, true);
    }

    /**
     * Password mutator that hashes the password field
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * Define embedded relationship with the Campaign Model
     */
    public function campaigns()
    {
        return $this->embedsMany('Northstar\Models\Campaign');
    }

    /**
     * Generate a token to authenticate a user
     *
     * @return mixed
     */
    public function login()
    {
        $token = Token::getInstance();
        $token->user_id = $this->_id;
        $token->save();

        return $token;
    }

    /**
     * Get the currently authenticated user from the session token.
     *
     * @return User
     */
    public static function current()
    {
        $token = Request::header('Session');
        $user = Token::userFor($token);

        return $user;
    }

    /**
     * Scope a query to get all of the users in a group.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroup($query, $id)
    {
        // Get signup group.
        return $query->where('campaigns', 'elemMatch', ['signup_id' => $id])
            ->orWhere('campaigns', 'elemMatch', ['signup_group' => $id])->get();
    }
}
