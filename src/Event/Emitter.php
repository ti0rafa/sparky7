<?php

namespace Sparky7\Event;

/**
 * Event Emitter Trait.
 */
trait Emitter
{
    protected $listeners = [];

    /**
     * Subscribe to an event.
     *
     * @param string   $eventName
     * @param callable $callBack
     */
    public function on($eventName, callable $callBack)
    {
        $this->listeners[$eventName] = $callBack;
    }

    /**
     * Emits an event.
     *
     * @param string $eventName
     * @param array  $arguments
     *
     * @return bool
     */
    public function emit($eventName, array $arguments = [])
    {
        if (isset($this->listeners[$eventName])) {
            return call_user_func_array($this->listeners[$eventName], $arguments);
        }
    }

    /**
     * Removes a specific listener from an event.
     *
     * If the listener could not be found, this method will return false. If it
     * was removed it will return true.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public function removeListener($eventName)
    {
        if (isset($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);

            return true;
        }

        return false;
    }
}
