<?php

namespace Sparky7\Property\Rule;

use Sparky7\Error\Exception\ExBadRequest;
use stdClass;

/**
 * Vector rule.
 */
class RuVector
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
        if (is_array($value) || is_object($value)) {
            return (array) self::vectorify($value);
        }

        return;
    }

    /**
     * Validate.
     */
    final public static function validate($value = null, $required = false, $default = null)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        $default = (count($default) > 0) ? $default : null;
        $value = (count($value) > 0) ? $value : null;

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid vector value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value)) {
            return $default;
        } else {
            return $value;
        }
    }

    /**
     * Vectorify an array or and object.
     *
     * @param array/object $data Data input
     *
     * @return array/object
     */
    final public static function vectorify($data)
    {
        if (count((array) $data) === 0) {
            return;
        }

        if (is_object($data) && get_class($data) === 'stdClass') {
            foreach ($data as $key => $value) {
                $data->$key = self::vectorify($value);
            }
        } elseif (is_array($data)) {
            if (array_key_exists(0, $data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = self::vectorify($value);
                }
            } else {
                $obj = new stdClass();
                foreach ($data as $key => $value) {
                    $obj->$key = self::vectorify($value);
                }

                return $obj;
            }

            return $data;
        }

        return $data;
    }
}
