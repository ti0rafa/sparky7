<?php

namespace Sparky7\Property\Rule;

use Exception;
use MongoDB\BSON\ObjectId;
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
        if ($value instanceof ObjectId) {
            return $value;
        } elseif (is_string($value) && mb_strlen($value) > 0) {
            try {
                return new ObjectId($value);
            } catch (Exception $Exception) {
                return;
            }
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
