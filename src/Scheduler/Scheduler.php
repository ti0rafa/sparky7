<?php

namespace Sparky7\Scheduler;

use Sparky7\Event\Emitter;
use Sparky7\Helper\Timer;
use Sparky7\Helper\Memory;
use DateTime;
use DateTimeZone;

/**
 * Scheduler.
 */
class Scheduler
{
    use Emitter;

    private $default_path;
    private $default_jobs;
    private $jobs;
    private $hour;
    private $minute;

    /**
     * Constructor.
     */
    final public function __construct()
    {
        $this->default_jobs = [];
        $this->jobs = [];

        $this->hour = (int) date('G');
        $this->minute = (int) date('i');
    }

    /**
     * Run when no other files where found on that specific time.
     *
     * @param string $file    File path
     * @param int    $timeout Timeout in seconds
     */
    final public function notFound($file, $timeout = 60)
    {
        $this->default_jobs[] = [
            'file' => $file,
            'stop' => false,
            'timeout' => $timeout,
        ];
    }

    /**
     * Run file at specific time.
     *
     * @param array  $at      Run job at time intervals
     * @param string $file    File path
     * @param int    $timeout Timeout in seconds
     * @param bool   $stop    Stop once run
     */
    final public function runAt(array $at, $file, $timeout = 60, $stop = true)
    {
        foreach ($at as $time) {
            $time = explode(':', $time);

            $this->jobs[(int) $time[0]][(int) $time[1]][] = [
                'file' => $file,
                'stop' => $stop,
                'timeout' => $timeout,
            ];
        }
    }

    /**
     * Run.
     */
    final public function run()
    {
        global $DI;

        $files = [];

        if (isset($this->jobs[$this->hour][$this->minute])) {
            $files = $this->jobs[$this->hour][$this->minute];
        } else {
            $files = $this->default_jobs;
        }

        // Run each cron
        foreach ($files as $key => $value) {
            if (!is_file($this->default_path.DIRECTORY_SEPARATOR.$value['file'])) {
                continue;
            }

            set_time_limit($value['timeout']);

            $Timer = new Timer();
            $Timer->start();

            $this->emit('before.run', [$value['file']]);

            require_once $this->default_path.DIRECTORY_SEPARATOR.$value['file'];

            $elapsed = $Timer->elapsed();

            $this->emit('after.run', [
                $elapsed,
                Memory::usage(true),
                Memory::usage(false),
            ]);
        }
    }

    /**
     * Set task path.
     *
     * @param string $path File path
     */
    final public function setPath($path)
    {
        $this->default_path = $path;
    }

    /**
     * Set Time Zone.
     *
     * @param string $time_zone Time Zone
     */
    final public function setTimeZone($time_zone = 'UTC')
    {
        $DateTime = new DateTime('now', new DateTimeZone($time_zone));

        $this->hour = (int) $DateTime->format('G');
        $this->minute = (int) $DateTime->format('i');
    }
}
