<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExForbidden extends Exception
{
    public function __construct($message = 'Forbidden', array $errors = null)
    {
        parent::__construct($message, 403, $errors);
    }
}
