<?php

namespace Sparky7\Api\Request;

/**
 * Request Headers.
 */
class Headers
{
    /**
     * Detect request headers.
     */
    public static function detect()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if ('HTTP_' == substr($name, 0, 5)) {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}
