<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * String rule.
 */
class RuString
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
        return ((is_string($value) && strlen($value) > 0) || is_numeric($value)) ? htmlspecialchars_decode(trim($value)) : null;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid string value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
