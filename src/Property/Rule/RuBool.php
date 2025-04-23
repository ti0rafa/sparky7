<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * Boolean rule.
 */
class RuBool
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
        if (is_bool($value)) {
            return (int) $value;
        } elseif ('false' === strtolower($value) ||
            'off' === strtolower($value) ||
            'no' === strtolower($value) ||
            '0' === $value ||
            0 === $value
        ) {
            return 0;
        } elseif ('true' === strtolower($value) ||
            'on' === strtolower($value) ||
            'yes' === strtolower($value) ||
            '1' === $value ||
            1 === $value
        ) {
            return 1;
        } else {
            return;
        }
    }

    /**
     * Validate.
     */
    public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid boolean value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
