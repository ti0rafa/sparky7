<?php

namespace Sparky7\Worker\Protocol;

use Sparky7\Worker\Protocol;
use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use Closure;

/**
 * Wrapper for AMQP C/PHP extension.
 */
class Amqp implements Protocol
{
    private $AMQPConnection;
    private $AMQPChannel;
    private $AMQPExchange;
    private $AMQPQueue;

    private $exchange_name;
    private $queue_name;

    /**
     * Construct.
     *
     * @param AMQPConnection $AMQPConnection Object
     * @param $exchange_name String Exchange name
     * @param $queue_name String Queue name
     */
    final public function __construct(AMQPConnection $AMQPConnection, $exchange_name, $queue_name, $routing_key, array $queue_arguments = [])
    {
        if (!$AMQPConnection->isConnected()) {
            $AMQPConnection->connect();
        }

        $this->exchange_name = $exchange_name;
        $this->queue_name = $queue_name;
        $this->routing_key = $routing_key;

        $this->AMQPChannel = new AMQPChannel($AMQPConnection);
        $this->AMQPChannel->setPrefetchCount(1);

        $this->AMQPExchange = new AMQPExchange($this->AMQPChannel);
        $this->AMQPExchange->setName($this->exchange_name);
        $this->AMQPExchange->setType(AMQP_EX_TYPE_DIRECT);
        $this->AMQPExchange->setFlags(AMQP_DURABLE);
        $this->AMQPExchange->declareExchange();

        $this->AMQPQueue = new AMQPQueue($this->AMQPChannel);
        $this->AMQPQueue->setName($this->queue_name);
        $this->AMQPQueue->setFlags(AMQP_DURABLE);
        foreach ($queue_arguments as $key => $value) {
            $this->AMQPQueue->setArgument($key, $value);
        }
        $this->AMQPQueue->declareQueue();

        $this->AMQPQueue->bind($this->exchange_name, $this->routing_key);
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

        return $this->AMQPExchange->publish($message, $this->routing_key, AMQP_NOPARAM, $attributes);
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
        return $this->AMQPQueue->ack($tag);
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
        return $this->AMQPQueue->consume($Closure);
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
        $flag = ($requeue) ? AMQP_REQUEUE : AMQP_NOPARAM;

        return $this->AMQPQueue->reject($tag, $flag);
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
