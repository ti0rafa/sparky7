<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * Email rule.
 */
class RuEmail
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
        $value = (is_string($value) && strlen($value) > 0) ? filter_var(trim($value), FILTER_VALIDATE_EMAIL) : null;
        $value = ($value !== false) ? $value : null;

        return $value;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid email value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value)) {
            return $default;
        } else {
            return $value;
        }
    }
}
