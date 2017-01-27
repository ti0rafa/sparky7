<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;
use Exception;

/**
 * MongoId rule.
 */
class RuMongoIdInArray
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
        $collection = (is_array($value)) ? $value : explode(',', $value);

        $data = [];
        foreach ($collection as $value) {
            if (is_string($value) && strlen($value) > 0) {
                try {
                    if (extension_loaded('mongodb')) {
                        $data[] = new \MongoDB\BSON\ObjectID($value);
                    } elseif (extension_loaded('mongo')) {
                        $data[] = new \MongoID($value);
                    }
                } catch (Exception $Exception) {
                    continue;
                }
            } elseif (is_object($value) && (get_class($value) === 'MongoId' || get_class($value) === 'MongoDB\BSON\ObjectID')) {
                $data[] = $value;
            } else {
                continue;
            }
        }

        return (count($data) > 0) ? $data : null;
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
