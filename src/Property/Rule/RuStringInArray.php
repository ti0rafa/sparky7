<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * String rule.
 */
class RuStringInArray
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
        $collection = (is_array($value)) ? $value : explode(',', $value);

        $data = [];
        foreach ($collection as $value) {
            if ((is_string($value) && strlen($value) > 0) || is_numeric($value)) {
                $data[] = htmlspecialchars_decode(trim($value));
            }
        }

        return (count($data) > 0) ? $data : null;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid string value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value)) {
            return $default;
        } else {
            return $value;
        }
    }
}
