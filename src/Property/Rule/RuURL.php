<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * URL rule.
 */
class RuURL
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
        $value = (is_string($value) && strlen($value) > 0) ? filter_var($value, FILTER_SANITIZE_URL) : null;
        $value = ($value !== false) ? $value : null;

        return $value;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid URL value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
