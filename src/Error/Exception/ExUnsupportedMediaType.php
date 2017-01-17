<?php

namespace Sparky7\Error\Exception;

use Sparky7\Error\Exception;

class ExUnsupportedMediaType extends Exception
{
    public function __construct($message = 'Unsupported media type', array $errors = null)
    {
        parent::__construct($message, 415, $errors);
    }
}
