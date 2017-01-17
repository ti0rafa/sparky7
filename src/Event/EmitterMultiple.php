<?php

namespace Sparky7\Event;

/**
 * Event Emitter Trait.
 */
trait EmitterMultiple
{
    protected $listeners = [];

    /**
     * Subscribe to an event.
     *
     * @param string   $eventName
     * @param callable $callBack
     * @param int      $priority
     */
    public function on($eventName, callable $callBack, $priority = 100)
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [
                true,  // If there's only one item, it's sorted
                [$priority],
                [$callBack],
            ];
        } else {
            $this->listeners[$eventName][0] = false; // marked as unsorted
            $this->listeners[$eventName][1][] = $priority;
            $this->listeners[$eventName][2][] = $callBack;
        }
    }

    /**
     * Subscribe to an event exactly once.
     *
     * @param string   $eventName
     * @param callable $callBack
     * @param int      $priority
     */
    public function once($eventName, callable $callBack, $priority = 100)
    {
        $wrapper = null;
        $wrapper = function () use ($eventName, $callBack, &$wrapper) {
            $this->removeListener($eventName, $wrapper);

            return call_user_func_array($callBack, func_get_args());
        };

        $this->on($eventName, $wrapper, $priority);
    }

    /**
     * Emits an event.
     *
     * This method will return true if 0 or more listeners were succesfully
     * handled. false is returned if one of the events broke the event chain.
     *
     * If the continueCallBack is specified, this callback will be called every
     * time before the next event handler is called.
     *
     * If the continueCallback returns false, event propagation stops. This
     * allows you to use the eventEmitter as a means for listeners to implement
     * functionality in your application, and break the event loop as soon as
     * some condition is fulfilled.
     *
     * Note that returning false from an event subscriber breaks propagation
     * and returns false, but if the continue-callback stops propagation, this
     * is still considered a 'successful' operation and returns true.
     *
     * @param string $eventName
     * @param array  $arguments
     *
     * @return bool
     */
    public function emit($eventName, array $arguments = [])
    {
        foreach ($this->listeners($eventName) as $listener) {
            call_user_func_array($listener, $arguments);
        }
    }

    /**
     * Returns the list of listeners for an event.
     *
     * The list is returned as an array, and the list of events are sorted by
     * their priority.
     *
     * @param string $eventName
     *
     * @return callable[]
     */
    public function listeners($eventName)
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }

        // The list is not sorted
        if (!$this->listeners[$eventName][0]) {
            // Sorting
            array_multisort($this->listeners[$eventName][1], SORT_NUMERIC, $this->listeners[$eventName][2]);

            // Marking the listeners as sorted
            $this->listeners[$eventName][0] = true;
        }

        return $this->listeners[$eventName][2];
    }

    /**
     * Removes a specific listener from an event.
     *
     * If the listener could not be found, this method will return false. If it
     * was removed it will return true.
     *
     * @param string   $eventName
     * @param callable $listener
     *
     * @return bool
     */
    public function removeListener($eventName, callable $listener)
    {
        if (!isset($this->listeners[$eventName])) {
            return false;
        }
        foreach ($this->listeners[$eventName][2] as $index => $check) {
            if ($check === $listener) {
                unset($this->listeners[$eventName][1][$index]);
                unset($this->listeners[$eventName][2][$index]);

                return true;
            }
        }

        return false;
    }

    /**
     * Removes all listeners.
     *
     * If the eventName argument is specified, all listeners for that event are
     * removed. If it is not specified, every listener for every event is
     * removed.
     *
     * @param string $eventName
     */
    public function removeAllListeners($eventName = null)
    {
        if (!is_null($eventName)) {
            unset($this->listeners[$eventName]);
        } else {
            $this->listeners = [];
        }
    }
}
