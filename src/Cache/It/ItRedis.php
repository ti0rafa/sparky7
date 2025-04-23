<?php

namespace Sparky7\Cache\It;

use Psr\SimpleCache\CacheInterface;
use Redis;

/**
 * Redis Cache Interface.
 */
class ItRedis implements CacheInterface
{
    private $Redis;
    private $ttl;

    /**
     * Construct.
     *
     * @param Redis $Redis Redis connection
     * @param int   $ttl   Time to live
     */
    public function __construct(Redis $Redis, $ttl)
    {
        $this->Redis = $Redis;

        $this->ttl = $ttl;
    }

    /**
     * Get key value.
     *
     * @param string $key     Key
     * @param any    $default Default key value
     *
     * @return any Key Value
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        $this->Redis->expire($key, $this->ttl);

        return $this->Redis->get($key);
    }

    /**
     * Set key value.
     *
     * @param string $key   Key
     * @param any    $value Key value
     * @param int    $ttl   Time to live
     */
    public function set($key, $value, $ttl = null)
    {
        if (is_null($ttl)) {
            $ttl = $this->ttl;
        }

        return $this->Redis->setEx($key, $ttl, $value);
    }

    /**
     * Delete key value.
     *
     * @param string $key Key
     */
    public function delete($key)
    {
        return $this->Redis->delete($key);
    }

    /**
     * Clear all keys.
     */
    public function clear()
    {
        return $this->Redis->flushDb();
    }

    /**
     * Get Multiple keys.
     *
     * @param Iterator $keys    Keys
     * @param any      $default Default value
     *
     * @return Iterator Key values
     */
    public function getMultiple($keys, $default = null)
    {
        $Iterator = [];
        foreach ($keys as $key) {
            $Iterator[$key] = $this->get($key, $default);
        }

        return $Iterator;
    }

    /**
     * Set multiple value keys.
     *
     * @param Iterator $keys Keys
     * @param int      $ttl  Time to live
     */
    public function setMultiple($keys, $ttl = null)
    {
        foreach ($keys as $key) {
            $this->set($key, $ttl);
        }

        return true;
    }

    /**
     * Delete multiple value keys.
     *
     * @param Iterator $keys Keys
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key, $ttl);
        }

        return true;
    }

    /**
     * Has key value.
     *
     * @param string $key Key
     */
    public function has($key)
    {
        return $this->Redis->exists($key);
    }
}
