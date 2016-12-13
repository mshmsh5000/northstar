<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Exception;
use InvalidArgumentException;
use Jenssegers\Mongodb\Eloquent\Model as BaseModel;
use MongoDB\BSON\UTCDateTime;

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

    /**
     * Convert a DateTime to a string that can be
     * stored in the database.
     *
     * @param  \DateTime|int  $value
     * @return string
     */
    public function fromDateTime($value)
    {
        $value = $this->asDateTime($value);

        return new UTCDateTime($value->getTimestamp() * 1000);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \DateTime|Carbon
     */
    protected function asDateTime($value)
    {
        try {
            return parent::asDateTime($value);
        } catch (InvalidArgumentException $e) {
            return $this->asDateTimeFallback($value);
        }
    }

    /**
     * Fallback to try to parse poorly formatted date strings, or
     * return `null` if it's hopeless.
     *
     * @param $value
     * @return Carbon|null
     */
    protected function asDateTimeFallback($value)
    {
        try {
            return Carbon::parse($value);
        } catch (Exception $e) {
            return null;
        }
    }
}
