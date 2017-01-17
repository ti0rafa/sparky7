<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;
use MongoDB\BSON\UTCDateTime;

/**
 * Timestamp rule.
 */
class RuTimestamp
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
        if ($value instanceof UTCDateTime) {
            return $value->sec;
        } elseif (is_numeric($value)) {
            return (int) $value;
        } elseif (is_string($value)) {
            return strtotime($value);
        } else {
            return;
        }
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid timestamp value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value)) {
            return $default;
        } else {
            return $value;
        }
    }
}
