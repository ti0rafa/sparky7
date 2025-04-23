<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * MongoId rule.
 */
class RuMongoIdInArray
{
    /**
     * Export value.
     */
    public static function export($value)
    {
        return (string) self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    public static function sanitize($value)
    {
        $collection = (is_array($value)) ? $value : explode(',', $value);

        $data = [];
        foreach ($collection as $value) {
            $value = RuMongoId::sanitize($value);

            if (null !== $value) {
                $data[] = $value;
            }
        }

        return (count($data) > 0) ? $data : null;
    }

    /**
     * Validate.
     */
    public static function validate($value = null, $required = false, $default = null, $use_default = true)
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
