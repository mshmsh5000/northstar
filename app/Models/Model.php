<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

/**
 * Base model class
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $field, string $comparison = '=', string $value)
 * @method static \Illuminate\Database\Eloquent\Builder find(string $id, array $columns=['*'])
 * @method static \Illuminate\Database\Eloquent\Builder findMany(array $ids)
 * @method static \Illuminate\Database\Eloquent\Builder findOrFail(string $id, array $columns=['*'])
 */
class Model extends BaseModel
{
    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // Drop field if attribute is empty string or null.
        if (empty($value)) {
            $this->drop($key);

            return null;
        }

        return parent::setAttribute($key, $value);
    }
}
