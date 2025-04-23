<?php

namespace Sparky7\Helper;

/**
 * Memory usage.
 */
class Memory
{
    /**
     * Memory usage.
     *
     * @param bool $real Real memory usage
     *
     * @return string Memory
     */
    public static function usage($real = false)
    {
        $mem_usage = memory_get_usage($real);

        if ($mem_usage < 1024) {
            return $mem_usage . ' b';
        } elseif ($mem_usage < 1048576) {
            return round($mem_usage / 1024, 2) . ' kb';
        } else {
            return round($mem_usage / 1048576, 2) . ' mb';
        }
    }

    /**
     * Memory peak.
     *
     * @param bool $real Real memory peak
     *
     * @return string Memory
     */
    public static function peak($real = false)
    {
        $mem_usage = memory_get_peak_usage($real);

        if ($mem_usage < 1024) {
            return $mem_usage . ' b';
        } elseif ($mem_usage < 1048576) {
            return round($mem_usage / 1024, 2) . ' kb';
        } else {
            return round($mem_usage / 1048576, 2) . ' mb';
        }
    }
}
