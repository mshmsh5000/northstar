<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

class ApiKey extends Model
{
    protected $primaryKey = '_id';

    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'api_keys';
}
