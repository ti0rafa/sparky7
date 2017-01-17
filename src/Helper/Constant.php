<?php

namespace Sparky7\Helper;

use Exception;

/**
 * Constant class - helps create variables only once.
 */
class Constant
{
    private $collection;

    /**
     * Constuct.
     */
    final public function __construct()
    {
        $this->collection = [];
    }

    /**
     * Get property.
     *
     * @param string $key Property name
     *
     * @return any Property value
     */
    final public function __get($key)
    {
        return (isset($this->collection[$key])) ? $this->collection[$key] : null;
    }

    /**
     * Set Property.
     *
     * @param string $key   Property name
     * @param any    $value Property value
     */
    final public function __set($key, $value)
    {
        if (isset($this->collection[$key])) {
            throw new Exception($key.' can\'t be modified');
        }

        $this->collection[$key] = $value;
    }

    /**
     * Has property.
     *
     * @param string $key Property name
     *
     * @return bool
     */
    final public function has($key)
    {
        return isset($this->collection[$key]);
    }
}
