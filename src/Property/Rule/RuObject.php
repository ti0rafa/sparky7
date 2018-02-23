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
    final public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        $default = (is_object($default) && !empty((array) $default)) ? $default : null;
        $value = (is_object($value) && !empty((array) $value)) ? $value : null;

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid object value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
