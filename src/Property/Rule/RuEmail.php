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
    public static function export($value)
    {
        return self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    public static function sanitize($value)
    {
        $value = (is_string($value) && strlen($value) > 0) ? filter_var(trim($value), FILTER_VALIDATE_EMAIL) : null;
        $value = (false !== $value) ? $value : null;

        return $value;
    }

    /**
     * Validate.
     */
    public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid email value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
