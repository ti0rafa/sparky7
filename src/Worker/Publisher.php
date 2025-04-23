<?php

namespace Sparky7\Worker;

/**
 * Publisher.
 */
class Publisher
{
    private $Protocol;

    /**
     * Construct.
     *
     * @param Protocol $Protocol Protocol Object
     */
    public function __construct(Protocol $Protocol)
    {
        $this->Protocol = $Protocol;
    }

    /**
     * Publish message.
     *
     * @param string $message    Message to be publish
     * @param array  $attributes Attributes
     *
     * @return bool
     */
    public function publish($message, array $attributes = [])
    {
        return $this->Protocol->publish($message, $attributes);
    }
}
