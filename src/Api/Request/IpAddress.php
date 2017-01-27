<?php

namespace Sparky7\Api\Request;

/**
 * Request IP Address.
 */
class IpAddress
{
    /**
     * Detect remote ip address.
     */
    final public static function detect($use_default = false)
    {
        /*
         * Cloudflare
         */

        if (!$use_default && isset($_SERVER['HTTP_CF_CONNECTION_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTION_IP'];
        }

        /*
         * Coyote Load balancer
         */

        if (!$use_default && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            $ip = explode(',', $ip);

            return (count($ip) > 1) ? $ip[0] : $ip;
        }

        /*
         * PHP default method
         */

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}
