<?php

namespace Sparky7\Event;

interface EmitterInterface
{
    public function on($eventName, callable $callBack, $priority);

    public function once($eventName, callable $callBack, $priority);

    public function emit($eventName, array $arguments);

    public function listeners($eventName);

    public function removeListener($eventName, callable $listener);

    public function removeAllListeners($eventName);
}
