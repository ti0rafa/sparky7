<?php

namespace Sparky7\Worker\Protocol;

use pbk\worker\Protocol;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use AMQPChannel;
use Closure;

/**
 * Wrapper for PhpAmqpLib.
 */
class PhpAmqpLib implements Protocol
{
    private $AMQPStreamConnection;
    private $AMQPChannel;
    private $AMQPExchange;
    private $AMQPQueue;

    private $exchange_name;
    private $queue_name;

    /**
     * Construct.
     *
     * @param AMQPStreamConnection $AMQPStreamConnection Object
     * @param $exchange_name String Exchange name
     * @param $queue_name String Queue name
     */
    final public function __construct(AMQPStreamConnection $AMQPStreamConnection, $exchange_name, $queue_name, $routing_key, array $queue_arguments = null)
    {
        $this->exchange_name = $exchange_name;
        $this->queue_name = $queue_name;
        $this->routing_key = $routing_key;

        $this->AMQPChannel = $AMQPStreamConnection->channel();
        $this->AMQPChannel->basic_qos(null, 1, null);

        $this->AMQPChannel->queue_declare($this->queue_name, false, true, false, false, false, $queue_arguments);

        $this->AMQPChannel->exchange_declare($this->exchange_name, 'direct', false, true, false);
        $this->AMQPChannel->queue_bind($this->queue_name, $this->exchange_name, $this->routing_key);
    }

    /**
     * Publish message.
     *
     * @param string $message    Message Id
     * @param array  $attributes Attributes
     *
     * @return bool
     */
    final public function publish($message, array $attributes = [])
    {
        // Set delivery mode
        if (!isset($attributes['delivery_mode'])) {
            $attributes['delivery_mode'] = 2;
        }

        $AMQPMessage = new AMQPMessage($message, $attributes);

        $this->AMQPChannel->basic_publish($AMQPMessage, $this->exchange_name, $this->routing_key);
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
        return $this->AMQPChannel->basic_ack($tag);
    }

    /**
     * Consume queue.
     *
     * @param Closure $Closure Callback
     *
     * @return bool
     */
    public function consume(Closure $Closure, $consumer_tag = '')
    {
        $this->AMQPChannel->basic_consume($this->queue_name, $consumer_tag, false, false, false, false, $Closure);

        while (count($this->AMQPChannel->callbacks)) {
            $this->AMQPChannel->wait();
        }
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
        return $this->AMQPChannel->basic_reject($tag, false, $requeue);
    }

    /**
     * Queue has  messages.
     *
     * @return int
     */
    final public function haveMessages()
    {
        return count($this->AMQPChannel->callbacks);
    }

    /**
     * Stop accepting new messages.
     *
     * @return bool
     */
    final public function wait()
    {
        return $this->AMQPChannel->wait();
    }
}
