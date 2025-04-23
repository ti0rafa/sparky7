<?php

namespace Sparky7\Property;

/**
 * Validator wrapper.
 */
class Validator
{
    /**
     * Create class namespace.
     *
     * @param string $class Class name
     *
     * @return string Namespace
     */
    private static function getNameSpace($class)
    {
        return '\\Sparky7\\Property\\Rule\\Ru' . $class;
    }

    /**
     * Export a value.
     *
     * @param string $type  Property type
     * @param any    $value Property value
     *
     * @return any Property value
     */
    public static function export($type, $value)
    {
        $namespace = self::getNameSpace($type);

        return $namespace::export($value);
    }

    /**
     * Santize a value.
     *
     * @param string $type  Property type
     * @param any    $value Property value
     *
     * @return any Property value
     */
    public static function sanitize($type, $value)
    {
        $namespace = self::getNameSpace($type);

        return $namespace::sanitize($value);
    }

    /**
     * Validate a value.
     *
     * @param string $type        Property type
     * @param any    $value       Property value
     * @param any    $required    Is required
     * @param any    $default     Property default value
     * @param bool   $use_default Use default
     *
     * @return any Property value
     */
    public static function validate($type, $value, $required = false, $default = null, $use_default = true)
    {
        $namespace = self::getNameSpace($type);

        return $namespace::validate($value, $required, $default, $use_default);
    }
}
