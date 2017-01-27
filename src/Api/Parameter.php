<?php

namespace Sparky7\Api;

use Sparky7\Property\Validator;

/**
 * Param class.
 */
class Parameter
{
    private $default;
    private $method;
    private $required;
    private $type;
    private $value;

    /**
     * Constructor.
     *
     * @param string $type    Property type
     * @param any    $default Default value
     */
    final public function __construct($type, $default = null)
    {
        $this->default = $default;
        $this->method = 'unset';
        $this->required = false;
        $this->type = $type;
        $this->value = null;
    }

    /**
     * Get method.
     *
     * @param string $key Parameter name
     *
     * @return string Parameter value
     */
    final public function __get($key)
    {
        return (isset($this->{$key})) ? $this->{$key} : null;
    }

    /**
     * Debug Info.
     *
     * @return array Debug array
     */
    final public function __debugInfo()
    {
        return [
            'method' => $this->method,
            'type' => $this->type,
            'value' => $this->value,
        ];
    }

    /**
     * Set object value.
     *
     * @param any    $value  Property value
     * @param string $method Set method
     */
    final public function set($value, $method = 'set')
    {
        $this->value = $value;
        $this->method = $method;

        return $this;
    }

    /**
     * Set property as required.
     */
    final public function required()
    {
        $this->required = !$this->required;

        return $this;
    }

    /**
     * Sanitize property.
     */
    final public function sanitize()
    {
        $this->value = Validator::sanitize($this->type, $this->value);

        return $this;
    }

    /**
     * Validate property.
     */
    final public function validate()
    {
        $this->value = Validator::validate($this->type, $this->value, $this->required, $this->default, true);

        return $this;
    }
}
