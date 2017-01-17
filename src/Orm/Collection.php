<?php

namespace Sparky7\Orm;

use Sparky7\Orm\Qb\MongoDB;
use Exception;
use OuterIterator;

/**
 * Collection class.
 */
abstract class Collection implements OuterIterator
{
    /*
     * OuterIterator.
     */

    private $Iterator;

    /*
     * Other properties.
     */

    private $QB;

    /*
     * DB variables.
     */

    protected $MongoDB;
    protected $collection;
    protected $entity;

    /**
     * Call method.
     *
     * @param string $method    Method name
     * @param array  $arguments Arguments
     *
     * @return any
     */
    final public function __call($method, array $arguments = null)
    {
        if (is_callable([$this->QB, $method])) {
            if ($method === 'find') {
                $this->Iterator = call_user_func_array([$this->QB, $method], $arguments);
            } else {
                return call_user_func_array([$this->QB, $method], $arguments);
            }
        }

        return false;
    }

    /**
     * Define events.
     */
    public function events()
    {
        return [];
    }

    /**
     * Init class.
     */
    final public function init()
    {
        // Select Query Builder
        if (get_class($this->MongoDB) === 'MongoDB\Database') {
            $this->QB = new Qb\MongoDB($this->MongoDB, $this->collection);
        } elseif (get_class($this->MongoDB) === 'MongoDB') {
            $this->QB = new Qb\Mongo($this->MongoDB, $this->collection);
        } else {
            throw new Exception('Invalid mongo database');
        }

        // Register events
        if (method_exists($this, 'events')) {
            foreach ($this->events() as $key => $value) {
                $this->QB->on($key, $value);
            }
        }
    }

    /**
     * Create a new empty entity.
     *
     * @return object Entity
     */
    final public function entity()
    {
        return new $this->entity($this->MongoDB->{$this->collection}, []);
    }

    /**
     * Exports collection into an array.
     *
     * @return array Collection data
     */
    public function toArray()
    {
        $collection = [];

        foreach ($this as $Entity) {
            $collection[] = $Entity->toArray();
        }

        return $collection;
    }

    /**
     * Get Next.
     */
    public function getNext()
    {
        if (get_class($this->MongoDB) === 'MongoDB') {
            $this->next();
        }

        $data = $this->current();

        return (is_null($data)) ? $this->entity() : $data;
    }

    /**
     * Get the current value.
     *
     * @return mixed
     */
    public function current()
    {
        if (is_null($this->Iterator)) {
            return;
        }

        $data = $this->Iterator->current();

        if (is_null($data)) {
            return;
        }

        return new $this->entity($this->MongoDB->{$this->collection}, (array) $data);
    }

    /**
     * Get key.
     *
     * @return int Iterator key
     */
    final public function key()
    {
        return $this->Iterator->key();
    }

    /**
     * Increments iterator key.
     *
     * @return bool
     */
    final public function next()
    {
        return $this->Iterator->next();
    }

    /**
     * Decrements iterator key.
     *
     * @return bool
     */
    final public function rewind()
    {
        return $this->Iterator->rewind();
    }

    /**
     * Check to see if current key is valid.
     *
     * @return bool
     */
    final public function valid()
    {
        return $this->Iterator->valid();
    }

    /**
     * Get Inner Iterator.
     *
     * @return [type] Iterator
     */
    final public function getInnerIterator()
    {
        return $this->Iterator;
    }
}
