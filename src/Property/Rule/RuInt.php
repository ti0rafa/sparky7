<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * Integer rule.
 */
class RuInt
{
    /**
     * Export value.
     */
    public static function export($value)
    {
        return self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    public static function sanitize($value)
    {
        return (is_numeric($value)) ? (int) $value : null;
    }

    /**
     * Validate.
     */
    public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid float value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
