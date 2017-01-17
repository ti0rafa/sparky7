<?php

namespace Sparky7\Worker;

use Closure;

/**
 * Consumer.
 */
class Consumer
{
    private $Protocol;

    /**
     * Construct.
     *
     * @param Protocol $Protocol Protocol Object
     */
    final public function __construct(Protocol $Protocol)
    {
        $this->Protocol = $Protocol;
    }

    /**
     * Consume queue.
     *
     * @param Closure $Closure Callback
     */
    final public function consume(Closure $Closure)
    {
        return $this->Protocol->consume($Closure);
    }

    /**
     * Stop accepting new messages.
     *
     * @return bool
     */
    final public function wait()
    {
        $this->Protocol->wait();
    }

    /**
     * Acknowledge message.
     *
     * @param string $tag Message Id
     *
     * @return bool
     */
    final public function acknowledge($tag)
    {
        return $this->Protocol->acknowledge($tag);
    }

    /**
     * Reject message.
     *
     * @param string $tag     Message Id
     * @param bool   $requeue Requeue message
     *
     * @return bool
     */
    final public function reject($tag, $requeue = false)
    {
        return $this->Protocol->reject($tag, $requeue);
    }
}
