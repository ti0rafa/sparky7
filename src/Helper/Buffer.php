<?php

namespace Sparky7\Helper;

class Buffer
{
    /**
     * Clear buffer.
     */
    final public static function clear()
    {
        if (ob_get_level() >= 1 && ob_get_length() > 0) {
            ob_end_clean();
        }
    }

    /**
     * Enable Gzip buffer.
     */
    final public static function gzip()
    {
        if (
            isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') &&
            !in_array('ob_gzhandler', ob_list_handlers())
        ) {
            ob_start('ob_gzhandler');
        } elseif (ob_get_level() === 0) {
            ob_start();
        }
    }
}
