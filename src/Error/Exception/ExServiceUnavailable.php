<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExServiceUnavailable extends Exception
{
    public function __construct($message = 'Service Unavailable', array $errors = null)
    {
        parent::__construct($message, 503, $errors);
    }
}
