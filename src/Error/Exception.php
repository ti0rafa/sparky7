<?php

namespace Sparky7\Error;

/**
 * Custom Exception.
 */
class Exception extends \Exception
{
    private $context;
    private $errors;

    /**
     * Construct.
     *
     * @param string     $message Message
     * @param code       $code    Exception code
     * @param array|null $errors  Errors list
     * @param array|null $context Context
     */
    public function __construct($message, $code, array $errors = null, array $context = null)
    {
        parent::__construct($message, $code);

        $this->context = $context;
        $this->errors = $errors;
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

    /**
     * Get errors.
     *
     * @return array Errors
     */
    final public function getErrors()
    {
        return $this->errors;
    }
}
