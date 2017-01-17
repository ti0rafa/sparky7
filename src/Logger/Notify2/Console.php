<?php

namespace Sparky7\Logger\Notify;

use Sparky7\Helper\CLI;
use Sparky7\Logger\LoggerNotify;

/**
 * Console Handler.
 */
class Console extends LoggerNotify
{
    /**
     * Constructor.
     *
     * @param string $title Title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * Sends log notification.
     */
    final public function send()
    {
        switch ($this->level) {
            case 'notice':
                $color = '42';
                break;
            case 'info':
            case 'debug':
                $color = '46';
                break;
            case 'alert':
            case 'warning':
                $color = '43';
                break;
            default:
                $color = '41';
                break;
        }

        $output = PHP_EOL;
        $output .= CLI::addColor($this->title.' '.$this->level, '0;36').PHP_EOL;
        $output .= CLI::addColor($this->message, $color).PHP_EOL;

        echo $output;
    }
}
