<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExError extends Exception
{
    public function __construct($message = 'Internal Error', array $errors = null)
    {
        parent::__construct($message, 500, $errors);
    }
}
