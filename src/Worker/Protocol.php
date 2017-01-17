<?php

namespace Sparky7\Worker;

use Closure;

/**
 * Interface.
 */
interface Protocol
{
    public function publish($message, array $attributes);

    public function acknowledge($tag);

    public function consume(Closure $Closure, $consumer_tag = '');

    public function reject($tag, $requeue);
}
