<?php

namespace Sparky7\Cache;

use MongoDB\BSON\ObjectId;
use Sparky7\Cache\It\ItRedis;
use Sparky7\Event\Emitter;

/**
 * Persistance cache class.
 */
abstract class Collection
{
    use Emitter;

    private $CacheInterface;

    private $algorithm;
    private $prefix;
    private $ttl;

    /**
     * Construct method.
     */
    public function __construct($Service, $prefix, $default_ttl = 1800, $default_algorithm = 'md5')
    {
        /*
         * Validate parameters
         */

        if (0 === strlen($prefix)) {
            throw new InvalidArgumentException('Prefix is missing');
        }

        if (!is_int($default_ttl)) {
            throw new InvalidArgumentException('Time to live must be an integer');
        }

        /*
         * Detect cache interface
         */

        switch (get_class($Service)) {
            case 'Redis':
                $this->CacheInterface = new ItRedis($Service, $default_ttl);
                break;
            default:
                throw new InvalidArgumentException('Invalid Cache Interface');
                break;
        }

        /*
         * Set default values
         */

        $this->algorithm = $default_algorithm;
        $this->prefix = $prefix;

        /*
         * Register events
         */

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
        return $this->get($key, null);
    }

    /**
     * Get custom value.
     *
     * @return any Value
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            $this->emit('before.get', [$key]);
        }

        return json_decode($this->CacheInterface->get($this->key($key, $default)));
    }

    /**
     * Create a custom key.
     *
     * @param string $key Key
     *
     * @return string Hash Key
     */
    public function key($key)
    {
        if (is_object($key) && $key instanceof ObjectId) {
            $key = (string) $key;
        }

        return $this->prefix . ':' . hash($this->algorithm, $key);
    }

    /**
     * Set value for a specific key.
     *
     * @param string $key Property name
     */
    public function set($key, array $value, $ttl = null)
    {
        return $this->CacheInterface->set($this->key($key), json_encode($value), $ttl);
    }

    /**
     * Remove key.
     *
     * @param string $key Property name
     */
    public function delete($key)
    {
        return $this->CacheInterface->delete($this->key($key));
    }

    /**
     * Checks the internal data to see if value exists.
     *
     * @param string $key Property name
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->CacheInterface->has($this->key($key));
    }
}
