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
    final public static function export($value)
    {
        return self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    final public static function sanitize($value)
    {
        if (is_bool($value)) {
            return (int) $value;
        } elseif (strtolower($value) === 'false' ||
            strtolower($value) === 'off' ||
            strtolower($value) === 'no' ||
            $value === '0' ||
            $value === 0
        ) {
            return 0;
        } elseif (strtolower($value) === 'true' ||
            strtolower($value) === 'on' ||
            strtolower($value) === 'yes' ||
            $value === '1' ||
            $value === 1
        ) {
            return 1;
        } else {
            return;
        }
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null, $use_default = true)
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
