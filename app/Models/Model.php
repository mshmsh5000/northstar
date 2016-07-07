<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

/**
 * Base model class
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $field, string $comparison = '=', string $value)
 * @method static \Illuminate\Database\Eloquent\Builder find(string $id, array $columns=['*'])
 * @method static \Illuminate\Database\Eloquent\Builder findOrFail(string $id, array $columns=['*'])
 */
class Model extends BaseModel
{
    // ...
}
