<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExNotFound extends Exception
{
    public function __construct($message = 'Not Found', array $errors = null)
    {
        parent::__construct($message, 404, $errors);
    }
}
