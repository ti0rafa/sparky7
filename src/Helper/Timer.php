<?php

namespace Sparky7\Helper;

/**
 * Timer class that gets the the elapsed time.
 */
class Timer
{
    private $start;

    /**
     * Starts the timer.
     */
    public function start()
    {
        $this->start = microtime(true);
    }

    /**
     * Gets the elapsed time.
     *
     * @return array Returns the elapse time in minutes, seconds and microseconds
     */
    public function elapsed()
    {
        return $this->returnTimer(microtime(true) - $this->start);
    }

    /**
     * Formats the return timer.
     *
     * @param float $milliseconds Microseconds
     *
     * @return array Returns the elapse time in minutes, seconds and microseconds
     */
    private function returnTimer($milliseconds)
    {
        $timer = (int) round($milliseconds * 1000);

        if ($timer < 1000) {
            return $timer . ' ms';
        } elseif ($timer < 60000) {
            return round($timer / 1000, 2) . ' s';
        } else {
            return round($timer / 60000, 2) . ' m';
        }
    }
}
