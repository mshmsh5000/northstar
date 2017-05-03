<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Jenssegers\Mongodb\Eloquent\Model as BaseModel;
use MongoDB\BSON\UTCDateTime;

/**
 * Base model class
 *
 * @method static \Jenssegers\Mongodb\Query\Builder where(string $field, string $comparison = '=', string $value)
 * @method static \Jenssegers\Mongodb\Query\Builder whereNull(string $field)
 * @method static \Jenssegers\Mongodb\Query\Builder find(string $id, array $columns=['*'])
 * @method static \Jenssegers\Mongodb\Query\Builder findMany(array $ids)
 * @method static \Jenssegers\Mongodb\Query\Builder findOrFail(string $id, array $columns=['*'])
 */
class Model extends BaseModel
{
    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        // Empty strings should be saved as `null`.
        if (empty($this->attributes[$key])) {
            $this->attributes[$key] = null;
        }

        return $this;
    }

    /**
     * Get the attributes that have been unset since the last sync.
     *
     * @return array
     */
    public function getClearable()
    {
        $clearable = [];

        foreach ($this->original as $key => $value) {
            if (! array_key_exists($key, $this->attributes) || is_null($this->attributes[$key])) {
                $clearable[$key] = null;
            }
        }

        return $clearable;
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = [])
    {
        // Remove `null` values from the attributes before inserting.
        $this->attributes = array_filter($this->attributes, function ($value) {
            return ! is_null($value);
        });

        return parent::performInsert($query, $options);
    }

    /**
     * Perform a model update operation.
     * @see \Illuminate\Database\Eloquent\Model::performUpdate()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performUpdate(Builder $query, array $options = [])
    {
        $success = parent::performUpdate($query, $options);
        $clearable = $this->getClearable();

        // If any attributes can be cleared, do so.
        if ($success && count($clearable) > 0) {
            $this->drop(array_keys($clearable));
        }

        return true;
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
