<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

class Campaign extends Model
{
    /**
     * Guarded attributes
     */
    protected $guarded = ['_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['_id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * The attributes which should be stored as MongoDate objects.
     * @see https://github.com/jenssegers/laravel-mongodb#dates
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Setting default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'drupal_id' => null,
        'reportback_id' => null,
        'reportback_source' => null,
        'signup_group' => null,
        'signup_id' => null,
        'signup_source' => null,
    ];
}
