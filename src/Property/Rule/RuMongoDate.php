<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;
use MongoDB\BSON\UTCDateTime;

/**
 * MongoDate rule.
 */
class RuMongoDate
{
    /**
     * Export value.
     */
    final public static function export($value)
    {
        $value = self::sanitize($value);

        return ($value instanceof UTCDateTime) ? (int) $value->toDateTime()->format('U') : null;
    }

    /**
     * Sanitize.
     */
    final public static function sanitize($value)
    {
        if ($value instanceof UTCDateTime) {
            return $value;
        } elseif (is_array($value) && isset($value['sec'])) {
            return new UTCDateTime((int) $value['sec']);
        } elseif (is_object($value) && isset($value->sec)) {
            return new UTCDateTime((int) $value->sec);
        } elseif (is_numeric($value)) {
            return new UTCDateTime((int) $value * 1000);
        } elseif (!is_null($value) && is_scalar($value)) {
            return new UTCDateTime(strtotime($value) * 1000);
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
            throw new ExBadRequest('Invalid date value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
