<?php

namespace Sparky7\Cache;

use Sparky7\Event\Emitter;
use Exception;
use Redis;

/**
 * Persistance cache class.
 */
abstract class Collection
{
    use Emitter;

    private $Redis;

    private $name;
    private $timeout;

    protected $row;

    /**
     * Construct method.
     */
    public function __construct(Redis $Redis, $name, $timeout = 1800)
    {
        $this->Redis = $Redis;

        $this->name = $name;
        $this->timeout = $timeout;

        // Select db
        if (is_null($this->name)) {
            throw new Exception('Undefined name in '.__CLASS__);
        }

        // Register events
        foreach ($this->events() as $key => $value) {
            $this->on($key, $value);
        }
    }

    /**
     * Gets value from memcache.
     *
     * @param string $key Property name
     *
     * @return any Value
     */
    public function __get($key)
    {
        if (is_object($key) && (is_a($key, 'MongoId') || is_a($key, 'MongoDB\BSON\ObjectID'))) {
            $key = (string) $key;
        }

        if (!$this->exists($key)) {
            $this->emit('before.get', ['_id' => $key]);
        }

        if (!$this->exists($key)) {
            return;
        }

        $this->Redis->expire($this->name.':'.$key, $this->timeout);

        return json_decode($this->Redis->get($this->name.':'.$key));
    }

    /**
     * Get custom value.
     *
     * @return any Value
     */
    public function get()
    {
        $key = hash('sha256', json_encode(func_get_args()));
        $args = array_merge([$key], func_get_args());

        if (!$this->exists($key)) {
            $this->emit('before.get', $args);
        }

        if (!$this->exists($key)) {
            return;
        }

        $this->Redis->expire($this->name.':'.$key, $this->timeout);

        return json_decode($this->Redis->get($this->name.':'.$key));
    }

    /**
     * Set value for a specific key.
     *
     * @param string $key Property name
     */
    public function setKeyValue($key, $value)
    {
        $this->Redis->setEx($this->name.':'.$key, $this->timeout, json_encode($value));
    }

    /**
     * Remove key.
     *
     * @param string $key Property name
     */
    public function removeKey($key)
    {
        $this->Redis->delete($this->name.':'.$key);
    }

    /**
     * Checks the internal data to see if value exists.
     *
     * @param string $key Property name
     *
     * @return bool
     */
    final public function exists($key)
    {
        if (is_object($key) && (get_class($key) === 'MongoId' || get_class($key) === 'MongoDB\BSON\ObjectID')) {
            $key = (string) $key;
        }

        return $this->Redis->exists($this->name.':'.$key);
    }
}
