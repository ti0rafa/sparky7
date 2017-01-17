<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExPayment extends Exception
{
    public function __construct($message = 'Payment Required', array $errors = null)
    {
        parent::__construct($message, 402, $errors);
    }
}
