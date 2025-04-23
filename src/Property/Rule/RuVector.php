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
    public static function export($value)
    {
        return self::sanitize($value);
    }

    /**
     * Sanitize.
     */
    public static function sanitize($value)
    {
        if (is_array($value) || is_object($value)) {
            return (array) self::vectorify($value);
        }

        return;
    }

    /**
     * Validate.
     */
    public static function validate($value = null, $required = false, $default = null, $use_default = true)
    {
        $default = self::sanitize($default);
        $value = self::sanitize($value);

        $default = (is_array($default) && !empty($default)) ? $default : null;
        $value = (is_array($value) && !empty($value)) ? $value : null;

        if (is_null($value) && is_null($default) && $required) {
            throw new ExBadRequest('Invalid vector value');
        } elseif (is_null($value) && is_null($default)) {
            return;
        } elseif (is_null($value) && $use_default) {
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
    public static function vectorify($data)
    {
        if (!is_array($data) || 0 === count((array) $data)) {
            return $data;
        }

        if (is_object($data) && 'stdClass' === get_class($data)) {
            foreach ($data as $key => $value) {
                $data->{$key} = self::vectorify($value);
            }
        } elseif (is_array($data)) {
            if (array_key_exists(0, $data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = self::vectorify($value);
                }
            } else {
                $obj = new stdClass();
                foreach ($data as $key => $value) {
                    $obj->{$key} = self::vectorify($value);
                }

                return $obj;
            }

            return $data;
        }

        return $data;
    }
}
