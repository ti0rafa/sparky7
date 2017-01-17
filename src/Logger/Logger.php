<?php

namespace Sparky7\Logger;

use Ramsey\Uuid\Uuid;
use Exception;

/**
 * Logger class.
 */
class Logger
{
    private $count;

    private $handlers;
    private $tags;

    public $pid;
    public $app;
    public $description;

    /**
     * Construct method.
     */
    final public function __construct()
    {
        $this->handlers = [];

        $this->clear();
    }

    /**
     * Clear data.
     */
    final public function clear()
    {
        $this->pid = (string) Uuid::uuid4();
        $this->count = 0;
        $this->app = null;
        $this->description = null;

        $this->tags = null;
    }

    /**
     * Process a new message.
     *
     * @param string $level     Level
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final private function process($level, $message, array $context = null, array $notifiers = null)
    {
        // Sanitize parameters
        $context = (is_array($context)) ? $context : [];
        $notifiers = (is_array($notifiers)) ? $notifiers : [];

        // Loop handlers and emits a notification
        foreach ($notifiers as $position) {
            if (isset($this->handlers[$position])) {
                // Clears previous values
                $this->handlers[$position]->clear();

                // Set values
                $this->handlers[$position]->pid = $this->pid;
                $this->handlers[$position]->app = $this->app;
                $this->handlers[$position]->description = $this->description;
                $this->handlers[$position]->tags = $this->tags;

                $this->handlers[$position]->count = $this->count;
                $this->handlers[$position]->level = $level;
                $this->handlers[$position]->message = $message;
                $this->handlers[$position]->context = $context;
                $this->handlers[$position]->errors = (isset($context['errors'])) ? $context['errors'] : null;

                // Sends notification
                try {
                    $this->handlers[$position]->send();
                } catch (Exception $Exception) {
                }
            }
        }

        ++$this->count;
    }

    /**
     * Adds a handler.
     *
     * @param string       $name         Notify handler name
     * @param LoggerNotify $LoggerNotify Notification object
     */
    final public function addNotifier($name, LoggerNotify $LoggerNotify)
    {
        $this->handlers[$name] = $LoggerNotify;
    }

    /**
     * Remove notifier.
     *
     * @param string $name Notify handler name
     */
    final public function removeNotifier($name)
    {
        if (isset($this->handlers[$name])) {
            unset($this->handlers[$name]);
        }
    }

    /**
     * Adds a tag.
     *
     * @param string $tag   Tag name
     * @param string $value Tag value
     */
    final public function addTag($key, $value)
    {
        if (!is_array($this->tags)) {
            $this->tags = [];
        }

        if (in_array($value, $this->tags) || is_null($value)) {
            return false;
        }

        $this->tags[$key] = $value;
    }

    /**
     * Removes a tag.
     *
     * @param string $tag Tag name
     */
    final public function removeTag($value)
    {
        foreach ($this->tags as $key => $tag) {
            if ($tag == $value) {
                unset($this->tags[$key]);
                $this->tags = array_values($this->tags);
            }
        }
    }

    /**
     * Clears all tags.
     */
    final public function clearTags()
    {
        $this->tags = [];
        $this->tags[] = $this->pid;
    }

    /**
     * Generates a alert logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function alert($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a critical logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function critical($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a emergency logger record.
     *
     * @param string $message   Message
     * @param array  $context   Log context data
     * @param array  $notifiers Selected Notifier
     */
    final public function emergency($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a debug logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function debug($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a error logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function error($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a info logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function info($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a notice logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function notice($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }

    /**
     * Generates a warning logger record.
     *
     * @param string $message   Message
     * @param array  $context   Context data
     * @param array  $notifiers Selected Notifier
     */
    final public function warning($message, array $context = null, array $notifiers = null)
    {
        $this->process(__FUNCTION__, $message, $context, $notifiers);
    }
}
