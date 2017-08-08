<?php

namespace pbk\cache;

use Exception;

class InvalidArgumentException extends Exception implements \Psr\SimpleCache\InvalidArgumentException
{
}
