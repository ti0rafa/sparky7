<?php

namespace Sparky7\Orm;

use Exception;

/**
 * Custom Exception.
 */
class OrmException extends Exception
{
    private $context;

    /**
     * Construct.
     *
     * @param string     $message Message
     * @param code       $code    Exception code
     * @param array|null $context Context
     */
    public function __construct($message, $code, array $context = null)
    {
        parent::__construct($message, $code);

        $this->context = $context;
    }

    /**
     * Get context.
     *
     * @return array Context
     */
    final public function getContext()
    {
        return $this->context;
    }
}
