<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model as Eloquent;

class ApiKey extends Eloquent
{
    protected $primaryKey = '_id';

    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'api_keys';
}
