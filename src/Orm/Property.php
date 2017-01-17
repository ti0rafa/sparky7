<?php

namespace Sparky7\Orm;

use Sparky7\Property\Validator;

class Property
{
    private $alias;
    private $default;
    private $ignore;
    private $method;
    private $previous;
    private $required;
    private $type;

    public $value;

    /**
     * Constructor.
     *
     * @param string $type    Property type
     * @param any    $default Default value
     */
    final public function __construct($type, $default = null)
    {
        $this->alias = null;
        $this->default = $default;
        $this->ignore = false;
        $this->method = 'unset';
        $this->previous = null;
        $this->required = false;
        $this->type = $type;
        $this->value = null;
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
     * Get property.
     *
     * @param string $key Property name
     *
     * @return any Property Value
     */
    final public function __get($key)
    {
        return (isset($this->{$key})) ? $this->{$key} : null;
    }

    /**
     * Set object value.
     *
     * @param any    $value  Property value
     * @param string $method Set method
     */
    final public function set($value, $method = 'set')
    {
        $this->previous = $this->value;
        $this->value = $value;
        $this->method = $method;

        return $this;
    }

    /**
     * Set alias.
     *
     * @param string $alias Alias
     */
    final public function alias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Set method.
     *
     * @param string $method Method
     */
    final public function method($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set property as ignore.
     */
    final public function ignore()
    {
        $this->ignore = !$this->ignore;

        return $this;
    }

    /**
     * Export scalar value.
     */
    final public function export()
    {
        return Validator::export($this->type, $this->value);
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
        $this->value = Validator::validate($this->type, $this->value, $this->required, $this->default);

        return $this;
    }
}
