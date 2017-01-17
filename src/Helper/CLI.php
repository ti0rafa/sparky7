<?php

namespace Sparky7\Helper;

/**
 * Cli methods
 */
class CLI
{
    /**
     * Add color to the string sent
     * @param  string $string String
     * @param  string $color  Color string (0,0)
     * @return string Returns formated string
     */
    final public static function addColor($string, $color)
    {
        return "\033[".$color."m".$string."\033[0m";
    }
}
