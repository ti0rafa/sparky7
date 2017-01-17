<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExUnauthorized extends Exception
{
    public function __construct($message = 'Unauthorized', array $errors = null)
    {
        parent::__construct($message, 401, $errors);
    }
}
