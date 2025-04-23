<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExBadRequest extends Exception
{
    public function __construct($message = 'Bad Request', ?array $errors = null)
    {
        parent::__construct($message, 400, $errors);
    }
}
