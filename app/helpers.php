<?php

use Northstar\Auth\Normalizer;

/**
 * Normalize the given value.
 *
 * @param string $type - The field to normalize
 * @param mixed $value - The value to be normalized
 * @return Normalizer|mixed
 */
function normalize($type = null, $value = null)
{
    $normalizer = app(Normalizer::class);

    // If no arguments given, return the normalizer instance.
    if (is_null($type)) {
        return $normalizer;
    }

    if (! method_exists($normalizer, $type)) {
        throw new InvalidArgumentException('There isn\'t a `'.$type.'` method on the normalizer ('.Normalizer::class.').');
    }

    // Otherwise, send the given value to the corresponding method.
    return $normalizer->{$type}($value);
}

/**
 * Check if the current route has any middleware attached.
 *
 * @param  null|string  $middleware
 * @return boolean
 */
function hasMiddleware($middleware = null)
{
    $currentRoute = app('router')->getCurrentRoute();

    if (! $currentRoute) {
        return false;
    }

    if ($middleware) {
        return in_array($middleware, $currentRoute->middleware());
    }

    return $currentRoute->middleware() ? true : false;
}
