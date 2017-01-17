<?php

namespace Sparky7\Logger;

/**
 * LoggerNotify class.
 */
abstract class LoggerNotify
{
    public $pid;
    public $title;
    public $description;
    public $tags;

    public $count;
    public $level;
    public $message;
    public $context;
    public $errors;

    /**
     * Sends log notification.
     */
    abstract public function send();

    /**
     * Clear previous values.
     */
    final public function clear()
    {
        $this->count = 0;
        $this->level = null;
        $this->message = null;
        $this->context = null;
    }
}
