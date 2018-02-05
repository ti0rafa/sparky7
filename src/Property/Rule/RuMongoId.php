<?php

namespace Sparky7\Property\Rule;

use Exception;
use Sparky7\Error\Exception\ExBadRequest;

/**
 * MongoId rule.
 */
class RuMongoId
{
    /**
     * Export value.
     */
    final public static function export($value)
    {
        return (string) self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    final public static function sanitize($value)
    {
        if (is_string($value) && strlen($value) > 0) {
            try {
                if (extension_loaded('mongodb')) {
                    return new \MongoDB\BSON\ObjectID($value);
                } elseif (extension_loaded('mongo')) {
                    return new \MongoID($value);
                }
            } catch (Exception $Exception) {
                return;
            }
        } elseif (is_object($value) && ('MongoId' === get_class($value) || 'MongoDB\BSON\ObjectID' === get_class($value))) {
            return $value;
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
            throw new ExBadRequest('Invalid ID value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
            return $default;
        } else {
            return $value;
        }
    }
}
