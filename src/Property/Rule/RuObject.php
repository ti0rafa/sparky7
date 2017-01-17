<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * Object rule.
 */
class RuObject
{
    /**
     * Export value.
     */
    final public static function export($value)
    {
        return self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    final public static function sanitize($value)
    {
        return ((is_array($value) || is_object($value))) ? (object) $value : null;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        $default = (count((array) $default) > 0) ? $default : null;
        $value = (count((array) $value) > 0) ? $value : null;

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid object value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value)) {
            return $default;
        } else {
            return $value;
        }
    }
}